<?php

namespace RoyalPanel\Http\Controllers\Auth;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use RoyalPanel\Models\User;
use RoyalPanel\Models\DiscordLink;
use RoyalPanel\Models\Discord2FACode;
use Illuminate\Http\JsonResponse;
use RoyalPanel\Facades\Activity;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LoginController extends AbstractLoginController
{
    /**
     * Handle all incoming requests for the authentication routes and render the
     * base authentication view component. React will take over at this point and
     * turn the login area into an SPA.
     */
    public function index(): View
    {
        return view('templates/auth.core');
    }

    /**
     * Handle a login request to the application.
     *
     * @throws \RoyalPanel\Exceptions\DisplayException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        try {
            $username = $request->input('user');

            /** @var User $user */
            $user = User::query()->where($this->getField($username), $username)->firstOrFail();
        } catch (ModelNotFoundException) {
            $this->sendFailedLoginResponse($request);
        }

        // Ensure that the account is using a valid username and password before trying to
        // continue. Previously this was handled in the 2FA checkpoint, however that has
        // a flaw in which you can discover if an account exists simply by seeing if you
        // can proceed to the next step in the login process.
        if (!password_verify($request->input('password'), $user->password)) {
            $this->sendFailedLoginResponse($request, $user);
        }

        if (!$user->use_totp) {
            return $this->sendLoginResponse($user, $request);
        }

        $discordLink = DiscordLink::where('user_id', $user->id)->whereNotNull('discord_id')->first();
        $hasDiscord2FA = $discordLink && $discordLink->discord_2fa_enabled;

        Activity::event('auth:checkpoint')->withRequestMetadata()->subject($user)->log();

        $request->session()->put('auth_confirmation_token', [
            'user_id' => $user->id,
            'token_value' => $token = Str::random(64),
            'expires_at' => CarbonImmutable::now()->addMinutes(5),
        ]);

        return new JsonResponse([
            'data' => [
                'complete' => false,
                'confirmation_token' => $token,
                'has_discord_2fa' => $hasDiscord2FA,
            ],
        ]);
    }

    public function sendDiscord2FA(Request $request): JsonResponse
    {
        $details = $request->session()->get('auth_confirmation_token');
        if (!$details || !isset($details['user_id'])) {
            return new JsonResponse(['error' => 'No active login session.'], 400);
        }

        $user = User::find($details['user_id']);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found.'], 404);
        }

        $link = DiscordLink::where('user_id', $user->id)->whereNotNull('discord_id')->first();
        if (!$link || !$link->discord_2fa_enabled) {
            return new JsonResponse(['error' => 'Discord 2FA is not enabled on this account.'], 400);
        }

        Discord2FACode::where('user_id', $user->id)->where('used', false)->where('sent', false)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = CarbonImmutable::now()->addMinutes(3);

        Discord2FACode::create([
            'user_id' => $user->id,
            'discord_id' => $link->discord_id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        return new JsonResponse(['success' => true, 'expires_at' => $expiresAt->toIso8601String()]);
    }
}
