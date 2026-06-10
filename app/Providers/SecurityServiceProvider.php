<?php

namespace Pterodactyl\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Psr\Log\LoggerInterface as Log;
use Pterodactyl\Services\Security\CloudflareService;
use Pterodactyl\Services\Security\IpBlockService;
use Pterodactyl\Services\Security\RateLimitService;
use Pterodactyl\Services\Security\AttackDetector;
use Pterodactyl\Services\Security\IpRotationService;
use Pterodactyl\Services\Security\HoneypotService;
use Pterodactyl\Services\Security\ServerProtectionService;
use Pterodactyl\Services\Security\AutoAttackResponseService;
use Pterodactyl\Services\Security\OriginProtectionService;
use Pterodactyl\Services\Security\BlackholeProtectionService;
        class SecurityServiceProvider extends ServiceProvider
{
    protected array $keys = [
        'security:cloudflare:enabled',
        'security:cloudflare:api_token',
        'security:cloudflare:zone_id',
        'security:rate_limiting:panel:enabled',
        'security:rate_limiting:panel:max_requests',
        'security:rate_limiting:api:enabled',
        'security:rate_limiting:api:max_requests',
        'security:rate_limiting:server:enabled',
        'security:rate_limiting:server:max_requests',
        'security:ip_blocking:auto_ban:enabled',
        'security:ip_blocking:auto_ban:max_failed_attempts',
        'security:ip_blocking:auto_ban:ban_duration_minutes',
        'security:ip_blocking:country_block:enabled',
        'security:attack_detection:enabled',
        'security:attack_detection:threshold:requests_per_ip_per_minute',
        'security:attack_detection:threshold:concurrent_connections_per_ip',
        'security:attack_detection:threshold:unique_ips_per_minute',
        'security:attack_detection:auto_actions:enable_under_attack_mode',
        'security:attack_detection:auto_actions:enable_bot_fight_mode',
        'security:attack_detection:auto_actions:block_offending_ips',
        'security:auto_response:enabled',
        'security:auto_response:grace_minutes',
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(config_path('security.php'), 'security');

        $this->app->singleton(CloudflareService::class, function () {
            return new CloudflareService();
        });

        $this->app->singleton(IpBlockService::class, function () {
            return new IpBlockService();
        });

        $this->app->singleton(RateLimitService::class, function () {
            return new RateLimitService();
        });

        $this->app->singleton(AttackDetector::class, function ($app) {
            return new AttackDetector(
                $app->make(CloudflareService::class),
                $app->make(IpBlockService::class),
                $app->make(RateLimitService::class),
                $app->make(AutoAttackResponseService::class),
            );
        });

        $this->app->singleton(IpRotationService::class, function ($app) {
            return new IpRotationService($app->make(CloudflareService::class));
        });

        $this->app->singleton(HoneypotService::class);
        $this->app->singleton(ServerProtectionService::class);

        $this->app->singleton(AutoAttackResponseService::class, function ($app) {
            return new AutoAttackResponseService(
                $app->make(CloudflareService::class),
                $app->make(ServerProtectionService::class),
                $app->make(IpBlockService::class),
            );
        });

        $this->app->singleton(BlackholeProtectionService::class);

        $this->app->singleton(OriginProtectionService::class, function ($app) {
        return new OriginProtectionService(
        $app->make(CloudflareService::class),
                $app->make(ServerProtectionService::class),
            );
        });
    }

    public function boot(Log $log): void
    {
        try {
            $values = DB::table('settings')
                ->where('key', 'like', 'settings::security:%')
                ->pluck('value', 'key')
                ->toArray();
        } catch (QueryException $exception) {
            $log->notice('Could not load security settings from database: ' . $exception->getMessage());
            return;
        }

        $map = [];
        foreach ($this->keys as $key) {
            $dbKey = 'settings::' . $key;
            $configKey = str_replace(':', '.', $key);
            $value = $values[$dbKey] ?? config(str_replace('security:', 'security.', $configKey));

            switch (strtolower((string) $value)) {
                case 'true': $value = true; break;
                case 'false': $value = false; break;
            }

            data_set($map, $configKey, $value);
        }

        if (!empty($map['security'] ?? [])) {
            config(['security' => array_merge(config('security'), $map['security'])]);
        }
    }
}
