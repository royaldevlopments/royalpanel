<?php

namespace RoyalPanel\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use RoyalPanel\Contracts\Repository\SettingsRepositoryInterface;

class BotAuthenticate
{
    public function __construct(
        private SettingsRepositoryInterface $settings,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $botToken = $this->settings->get('settings::royal:botToken', '');
        $headerToken = $request->bearerToken() ?? $request->header('X-Bot-Token');

        if ($headerToken && $headerToken === $botToken) {
            return $next($request);
        }

        if ($request->user()) {
            return $next($request);
        }

        return response()->json(['error' => 'Unauthenticated'], 401);
    }
}
