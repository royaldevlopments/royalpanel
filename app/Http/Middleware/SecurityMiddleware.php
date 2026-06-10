<?php

namespace Pterodactyl\Http\Middleware;

use Closure;
use Pterodactyl\Services\Security\AttackDetector;
use Pterodactyl\Services\Security\IpBlockService;
use Pterodactyl\Services\Security\RateLimitService;
use Pterodactyl\Services\Security\HoneypotService;

class SecurityMiddleware
{
    public function __construct(
        private AttackDetector $attackDetector,
        private IpBlockService $ipBlock,
        private RateLimitService $rateLimit,
        private HoneypotService $honeypot,
    ) {}

    public function handle($request, Closure $next, string $scope = 'panel')
    {
        $ip = $request->ip();
        $route = $request->path();
        $method = $request->method();

        if ($this->ipBlock->isBlocked($ip)) {
            abort(403, 'Your IP has been blocked.');
        }

        if ($this->ipBlock->isCountryBlocked($ip)) {
            abort(403, 'Access from your country is restricted.');
        }

        if ($this->honeypot->isHoneypotPath($route)) {
            $this->honeypot->recordHit($ip, $route, $request->userAgent() ?? '');
            abort(404);
        }

        $rateCheck = match ($scope) {
            'api' => $this->rateLimit->isApiRateLimited($ip),
            'server' => $this->rateLimit->isServerRateLimited($ip),
            default => $this->rateLimit->isPanelRateLimited($ip),
        };

        if (!$rateCheck['allowed']) {
            $this->rateLimit->logAttempt($ip, $route, $method, $rateCheck['count'], true);

            if (config('security.ip_blocking.auto_ban.enabled')) {
                $window = config('security.ip_blocking.auto_ban.attempt_window_minutes');
                $maxFailed = config('security.ip_blocking.auto_ban.max_failed_attempts');
                $failedAttempts = $this->rateLimit->getFailedAttempts($ip, $window);

                if ($failedAttempts >= $maxFailed) {
                    $duration = config('security.ip_blocking.auto_ban.ban_duration_minutes');
                    $this->ipBlock->blockIp($ip, 'Auto-banned: Rate limit exceeded', 'auto', $duration);
                }
            }

            abort(429, 'Too many requests. Please try again later.');
        }

        $this->rateLimit->logAttempt($ip, $route, $method, $rateCheck['count'], false);
        $this->attackDetector->incrementIpRequestCount($ip);

        $threat = $this->attackDetector->analyze($ip, $route, $method, $request->headers->all());

        if ($threat['threat'] && in_array('ip_blocked', $threat['actions'])) {
            abort(403, 'Security threat detected. Access blocked.');
        }

        return $next($request);
    }
}
