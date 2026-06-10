<?php

namespace Pterodactyl\Services\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AttackDetector
{
    public function __construct(
        private CloudflareService $cloudflare,
        private IpBlockService $ipBlock,
        private RateLimitService $rateLimit,
        private AutoAttackResponseService $autoResponse,
    ) {}

    public function analyze(string $ip, string $route, string $method, array $headers): array
    {
        if (!config('security.attack_detection.enabled')) {
            return ['threat' => false];
        }

        $threats = [];
        $threshold = config('security.attack_detection.threshold');

        $ipRequestCount = $this->getIpRequestCount($ip);
        if ($ipRequestCount >= $threshold['requests_per_ip_per_minute']) {
            $threats[] = 'high_request_rate';
        }

        $concurrentCount = $this->getConcurrentConnections($ip);
        if ($concurrentCount >= $threshold['concurrent_connections_per_ip']) {
            $threats[] = 'high_concurrency';
        }

        $uniqueIps = $this->getUniqueIpsPerMinute();
        if ($uniqueIps >= $threshold['unique_ips_per_minute']) {
            $threats[] = 'mass_requests';
        }

        if ($this->isDdosPattern($ip)) {
            $threats[] = 'ddos_pattern';
        }

        if (empty($threats)) {
            return ['threat' => false];
        }

        return $this->handleThreat($ip, $threats);
    }

    public function incrementIpRequestCount(string $ip): void
    {
        $key = "security:requests:{$ip}";
        Cache::increment($key);
        Cache::expire($key, 60);
    }

    private function getIpRequestCount(string $ip): int
    {
        return (int) Cache::get("security:requests:{$ip}", 0);
    }

    private function getConcurrentConnections(string $ip): int
    {
        return DB::table('security_rate_logs')
            ->where('ip', $ip)->where('logged_at', '>', now()->subMinute())
            ->count();
    }

    private function getUniqueIpsPerMinute(): int
    {
        return DB::table('security_rate_logs')
            ->where('logged_at', '>', now()->subMinute())
            ->distinct('ip')->count('ip');
    }

    private function isDdosPattern(string $ip): bool
    {
        return DB::table('security_attack_logs')
            ->where('ip', $ip)->where('type', 'ddos')
            ->where('detected_at', '>', now()->subMinutes(5))
            ->count() >= 3;
    }

    private function handleThreat(string $ip, array $threats): array
    {
        $severity = $this->calculateSeverity($threats);
        $actions = $this->determineActions($ip, $threats);
        $this->logAttack($ip, $threats, $severity, $actions);
        return ['threat' => true, 'severity' => $severity, 'threats' => $threats, 'actions' => $actions];
    }

    private function calculateSeverity(array $threats): string
    {
        $score = count($threats);
        return match (true) {
            $score >= 4 => 'critical',
            $score >= 3 => 'high',
            $score >= 2 => 'medium',
            default => 'low',
        };
    }

    private function determineActions(string $ip, array $threats): array
    {
        $actions = [];
        $autoActions = config('security.attack_detection.auto_actions');

        if ($autoActions['block_offending_ips'] ?? false) {
            $this->ipBlock->blockIp($ip, 'Attack detected: ' . implode(', ', $threats), 'auto', 60);
            $actions[] = 'ip_blocked';
            if ($this->cloudflare->enabled()) {
                $this->cloudflare->blockIp($ip, 'Attack detected');
                $actions[] = 'cloudflare_blocked';
            }
        }
        if ($autoActions['enable_under_attack_mode'] ?? false) {
            $this->cloudflare->setUnderAttackMode(true);
            $actions[] = 'under_attack_mode';
        }
        if ($autoActions['enable_bot_fight_mode'] ?? false) {
            $this->cloudflare->setBotFightMode(true);
            $actions[] = 'bot_fight_mode';
        }
        if (config('security.auto_response.enabled') && in_array('ddos_pattern', $threats)) {
            $this->autoResponse->activate();
            $actions[] = 'auto_response_activated';
        }
        return $actions;
    }

    private function logAttack(string $ip, array $threats, string $severity, array $actions): void
    {
        DB::table('security_attack_logs')->insert([
            'type' => $threats[0] ?? 'unknown', 'severity' => $severity, 'ip' => $ip,
            'details' => implode(', ', $threats),
            'metadata' => json_encode(['threats' => $threats, 'actions' => $actions]),
            'action_taken' => implode(', ', $actions), 'detected_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Log::warning("Attack detected from {$ip}: " . implode(', ', $threats));
    }

    public function getAttackStats(): array
    {
        return [
            'total_attacks' => DB::table('security_attack_logs')->count(),
            'attacks_today' => DB::table('security_attack_logs')->whereDate('detected_at', today())->count(),
            'active_blocks' => $this->ipBlock->getTotalBlocked(),
            'recent_attacks' => DB::table('security_attack_logs')->orderBy('detected_at', 'desc')->take(20)->get()->toArray(),
            'attack_types' => DB::table('security_attack_logs')->select('type', DB::raw('count(*) as count'))->groupBy('type')->orderBy('count', 'desc')->get()->toArray(),
        ];
    }

    public function getAttackLogs(int $page = 1, int $perPage = 50): array
    {
        $query = DB::table('security_attack_logs')->orderBy('detected_at', 'desc');
        $total = $query->count();
        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get()->toArray();
        return ['items' => $items, 'total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => ceil($total / $perPage)];
    }

    public function cleanup(int $retentionDays): void
    {
        DB::table('security_attack_logs')->where('detected_at', '<', now()->subDays($retentionDays))->delete();
    }
}
