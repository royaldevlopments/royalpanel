<?php

namespace Pterodactyl\Services\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RateLimitService
{
    public function check(string $key, int $maxAttempts, int $decayMinutes): array
    {
        $cacheKey = "security_rate_limit:{$key}";
        $current = Cache::get($cacheKey, ['count' => 0, 'blocked' => false]);

        if ($current['blocked']) {
            return ['allowed' => false, 'blocked' => true, 'remaining' => 0];
        }

        $current['count']++;
        $current['remaining'] = max(0, $maxAttempts - $current['count']);

        if ($current['count'] >= $maxAttempts) {
            $current['blocked'] = true;
        }

        Cache::put($cacheKey, $current, now()->addMinutes($decayMinutes));

        return [
            'allowed' => !$current['blocked'],
            'blocked' => $current['blocked'],
            'remaining' => $current['remaining'],
            'count' => $current['count'],
        ];
    }

    public function logAttempt(string $ip, string $route, string $method, int $attempts, bool $blocked): void
    {
        DB::table('security_rate_logs')->insert([
            'ip' => $ip, 'route' => $route, 'method' => $method,
            'attempts' => $attempts, 'blocked' => $blocked, 'logged_at' => now(),
        ]);
    }

    public function getFailedAttempts(string $ip, int $windowMinutes): int
    {
        return DB::table('security_rate_logs')
            ->where('ip', $ip)->where('blocked', true)
            ->where('logged_at', '>', now()->subMinutes($windowMinutes))
            ->count();
    }

    public function isPanelRateLimited(string $ip): array
    {
        if (!config('security.rate_limiting.panel.enabled')) {
            return ['allowed' => true, 'count' => 0];
        }
        return $this->check("panel:{$ip}", config('security.rate_limiting.panel.max_requests', 60), config('security.rate_limiting.panel.decay_minutes', 1));
    }

    public function isApiRateLimited(string $ip): array
    {
        if (!config('security.rate_limiting.api.enabled')) {
            return ['allowed' => true, 'count' => 0];
        }
        return $this->check("api:{$ip}", config('security.rate_limiting.api.max_requests', 120), config('security.rate_limiting.api.decay_minutes', 1));
    }

    public function isServerRateLimited(string $ip): array
    {
        if (!config('security.rate_limiting.server.enabled')) {
            return ['allowed' => true, 'count' => 0];
        }
        return $this->check("server:{$ip}", config('security.rate_limiting.server.max_requests', 100), config('security.rate_limiting.server.decay_minutes', 1));
    }

    public function cleanupOldLogs(int $retentionDays): void
    {
        DB::table('security_rate_logs')->where('logged_at', '<', now()->subDays($retentionDays))->delete();
    }
}
