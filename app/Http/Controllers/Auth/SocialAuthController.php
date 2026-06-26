<?php

namespace RoyalPanel\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Laravel\Socialite\Facades\Socialite;
use RoyalPanel\Models\User;
use RoyalPanel\Models\OAuthLink;
use RoyalPanel\Http\Controllers\Controller;
use RoyalPanel\Services\Users\UserCreationService;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;

class SocialAuthController extends Controller
{
    private array $providers = ['discord', 'github', 'google'];

    public function __construct(
        private SettingsRepositoryInterface $settings,
    ) {}

    public function index(): View
    {
        return view('templates/auth.core');
    }

    public function redirect(string $provider)
    {
        if (!in_array($provider, $this->providers)) {
            abort(404);
        }

        $enabled = $this->settings->get("settings::royal:oauth_{$provider}_enabled", false);
        if ($enabled !== 'true' && $enabled !== true) {
            abort(403, "$provider login is disabled.");
        }

        $clientId = $this->settings->get("settings::royal:oauth_{$provider}_client_id", '');
        $clientSecret = $this->settings->get("settings::royal:oauth_{$provider}_client_secret", '');

        config(["services.$provider" => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => url("/auth/oauth/$provider/callback"),
        ]]);

        return Socialite::driver($provider)
            ->scopes($this->getScopes($provider))
            ->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        if (!in_array($provider, $this->providers)) {
            abort(404);
        }

        $enabled = $this->settings->get("settings::royal:oauth_{$provider}_enabled", false);
        if ($enabled !== 'true' && $enabled !== true) {
            abort(403, "$provider login is disabled.");
        }

        $clientId = $this->settings->get("settings::royal:oauth_{$provider}_client_id", '');
        $clientSecret = $this->settings->get("settings::royal:oauth_{$provider}_client_secret", '');

        config(["services.$provider" => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => url("/auth/oauth/$provider/callback"),
        ]]);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/auth/login')->with('error', 'Failed to authenticate with ' . ucfirst($provider) . '.');
        }

        $existingLink = OAuthLink::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingLink) {
            \Illuminate\Support\Facades\Auth::guard()->login($existingLink->user, true);
            $request->session()->regenerate();
            return redirect('/');
        }

        if ($request->user()) {
            OAuthLink::create([
                'user_id' => $request->user()->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'data' => $socialUser->getRaw(),
            ]);
            return redirect('/account');
        }

        $email = $socialUser->getEmail();
        if (!$email) {
            return redirect('/auth/login')->with('error', 'No email returned from ' . ucfirst($provider) . '.');
        }

        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            OAuthLink::create([
                'user_id' => $existingUser->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'data' => $socialUser->getRaw(),
            ]);
            \Illuminate\Support\Facades\Auth::guard()->login($existingUser, true);
            $request->session()->regenerate();
            return redirect('/');
        }

        $registrationEnabled = $this->settings->get('settings::royal:registration', false);
        if ($registrationEnabled !== 'true' && $registrationEnabled !== true) {
            return redirect('/auth/login')->with('error', 'Registration is closed. Ask an admin to create an account.');
        }

        $username = $socialUser->getNickname() ?? $socialUser->getName() ?? explode('@', $email)[0];
        $username = preg_replace('/[^a-zA-Z0-9_.-]/', '', $username);
        $username = substr($username, 0, 20) ?: 'user' . Str::random(4);

        $baseUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = substr($baseUsername, 0, 16) . $counter;
            $counter++;
        }

        $nameParts = explode(' ', $socialUser->getName() ?? $username, 2);
        $firstName = $nameParts[0] ?? $username;
        $lastName = $nameParts[1] ?? '';

        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'email' => $email,
            'username' => $username,
            'name_first' => $firstName,
            'name_last' => $lastName,
            'password' => bcrypt(Str::random(40)),
        ]);

        $user->assignRole('user');

        event(new Registered($user));

        OAuthLink::create([
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'data' => $socialUser->getRaw(),
        ]);

        \Illuminate\Support\Facades\Auth::guard()->login($user, true);
        $request->session()->regenerate();
        return redirect('/');
    }

    private function getScopes(string $provider): array
    {
        return match ($provider) {
            'discord' => ['identify', 'email'],
            'github' => ['read:user', 'user:email'],
            'google' => ['openid', 'profile', 'email'],
            default => [],
        };
    }
}
