<?php

namespace RoyalPanel\Services\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HoneypotService
{
    protected array $honeypotPaths = [
        '/wp-admin', '/wp-login.php', '/xmlrpc.php', '/administrator',
        '/admin/login.php', '/.env', '/wp-content', '/shell.php',
        '/cmd.php', '/backup.zip', '/database.sql', '/phpmyadmin',
        '/_ignition', '/vendor/phpunit', '/.git/config', '/aws.yml',
        '/config.json', '/debug', '/test.php', '/info.php', '/proxy',
        '/cgi-bin', '/.aws/credentials', '/composer.json.backup',
        '/storage/logs/laravel.log',
    ];

    public function isHoneypotPath(string $path): bool
    {
        foreach ($this->honeypotPaths as $hPath) {
            if (str_starts_with(strtolower($path), $hPath)) {
                return true;
            }
        }
        return false;
    }

    public function recordHit(string $ip, string $path, string $userAgent): void
    {
        $key = "honeypot:{$ip}";
        $hits = Cache::get($key, 0);
        $hits++;
        Cache::put($key, $hits, now()->addDay());

        Log::warning("Honeypot hit from {$ip} on {$path} (UA: {$userAgent})");

        if ($hits >= 3) {
            $this->autoBlock($ip, $hits);
        }
    }

    public function getHitCount(string $ip): int
    {
        return Cache::get("honeypot:{$ip}", 0);
    }

    public function getTotalHits(): int
    {
        return count(Cache::get('honeypot:ips', []));
    }

    public function getRecentHits(int $limit = 50): array
    {
        return DB::table('security_attack_logs')
            ->where('type', 'honeypot')
            ->orderBy('detected_at', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    private function autoBlock(string $ip, int $hits): void
    {
        $ipBlock = app(IpBlockService::class);
        $ipBlock->blockIp($ip, "Honeypot triggered {$hits} times", 'auto', 1440);

        DB::table('security_attack_logs')->insert([
            'type' => 'honeypot', 'severity' => 'high', 'ip' => $ip,
            'details' => "Honeypot path accessed {$hits} times",
            'action_taken' => 'ip_blocked', 'detected_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        if (app(CloudflareService::class)->enabled()) {
            app(CloudflareService::class)->blockIp($ip, 'Honeypot auto-block');
        }
    }
}
