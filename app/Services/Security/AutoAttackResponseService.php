<?php

namespace RoyalPanel\Services\Security;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoAttackResponseService
{
    const CACHE_ATTACK_KEY = 'security:attack:active';
    const CACHE_SNAPSHOT_KEY = 'security:attack:snapshot';
    const CACHE_LAST_ATTACK_KEY = 'security:attack:last_seen';
    const CACHE_DURATION = 1440;

    public function __construct(
        private CloudflareService $cloudflare,
        private ServerProtectionService $server,
        private IpBlockService $ipBlock,
    ) {}

    public function isUnderAttack(): bool
    {
        return Cache::get(self::CACHE_ATTACK_KEY, false);
    }

    public function activate(): array
    {
        if ($this->isUnderAttack()) {
            return ['status' => 'already_active'];
        }

        $snapshot = $this->takeSnapshot();
        $actions = [];

        if ($this->cloudflare->enabled()) {
            $actions[] = $this->cloudflare->enableLockdownMode();
            $actions['lockdown_enabled'] = true;
            $this->cloudflare->setUnderAttackMode(true);
            $actions['under_attack_mode'] = true;
            $this->cloudflare->setBotFightMode(true);
            $actions['bot_fight_mode'] = true;
            $this->cloudflare->setSecurityLevel('high');
            $actions['waf_high'] = true;
            $this->cloudflare->setBrowserIntegrityCheck(true);
            $actions['browser_integrity'] = true;
            $this->cloudflare->setChallengePassage(300);
            $actions['challenge_passage'] = true;
        }

        $this->server->enableAllProtection();
        $actions['server_protection'] = true;

        Cache::forever(self::CACHE_SNAPSHOT_KEY, $snapshot);
        Cache::forever(self::CACHE_ATTACK_KEY, true);
        Cache::forever(self::CACHE_LAST_ATTACK_KEY, now()->timestamp);

        $this->logEvent('attack_started', $actions);

        return ['status' => 'activated', 'snapshot' => $snapshot, 'actions' => $actions];
    }

    public function deactivate(): array
    {
        if (!$this->isUnderAttack()) {
            return ['status' => 'already_normal'];
        }

        $snapshot = Cache::get(self::CACHE_SNAPSHOT_KEY, []);
        $actions = [];

        if ($this->cloudflare->enabled()) {
            $this->cloudflare->setUnderAttackMode($snapshot['under_attack_mode'] ?? false);
            $actions['under_attack_restored'] = true;
            $this->cloudflare->setBotFightMode($snapshot['bot_fight_mode'] ?? false);
            $actions['bot_fight_restored'] = true;
            $this->cloudflare->setSecurityLevel($snapshot['security_level'] ?? 'medium');
            $actions['waf_restored'] = true;
            if (!($snapshot['lockdown_enabled'] ?? false)) {
                $this->cloudflare->disableLockdownMode();
                $actions['lockdown_disabled'] = true;
            }
            $this->cloudflare->setBrowserIntegrityCheck($snapshot['browser_integrity_check'] ?? false);
            $actions['browser_integrity_restored'] = true;
        }

        Cache::forget(self::CACHE_ATTACK_KEY);
        Cache::forget(self::CACHE_SNAPSHOT_KEY);
        Cache::forget(self::CACHE_LAST_ATTACK_KEY);

        $this->logEvent('attack_ended', $actions);

        return ['status' => 'deactivated', 'restored' => $actions];
    }

    public function stillUnderAttack(int $graceMinutes = 5): bool
    {
        $lastSeen = Cache::get(self::CACHE_LAST_ATTACK_KEY);
        if (!$lastSeen) return false;
        return now()->diffInMinutes(now()->setTimestamp($lastSeen)) < $graceMinutes;
    }

    public function getStatus(): array
    {
        $active = $this->isUnderAttack();
        $snapshot = Cache::get(self::CACHE_SNAPSHOT_KEY, []);
        $lastSeen = Cache::get(self::CACHE_LAST_ATTACK_KEY);

        return [
            'under_attack' => $active,
            'started_at' => $active && $lastSeen ? now()->setTimestamp($lastSeen)->toIso8601String() : null,
            'duration_minutes' => $active && $lastSeen ? now()->diffInMinutes(now()->setTimestamp($lastSeen)) : 0,
            'protections_active' => $active,
            'snapshot_exists' => !empty($snapshot),
            'snapshot' => $snapshot,
        ];
    }

    public function getActiveBlockCount(): int
    {
        return DB::table('security_blocked_ips')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();
    }

    private function takeSnapshot(): array
    {
        if (!$this->cloudflare->enabled()) {
            return ['cloudflare_enabled' => false, 'timestamp' => now()->toIso8601String()];
        }
        return [
            'cloudflare_enabled' => true, 'under_attack_mode' => true,
            'bot_fight_mode' => true, 'security_level' => 'high',
            'browser_integrity_check' => true, 'challenge_passage' => 300,
            'lockdown_enabled' => false, 'timestamp' => now()->toIso8601String(),
        ];
    }

    private function logEvent(string $event, array $data): void
    {
        Log::info("Security: {$event}", $data);
        DB::table('security_attack_logs')->insert([
            'type' => 'auto_response', 'severity' => $event === 'attack_started' ? 'critical' : 'info',
            'ip' => '0.0.0.0', 'details' => $event, 'metadata' => json_encode($data),
            'action_taken' => $event, 'detected_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
