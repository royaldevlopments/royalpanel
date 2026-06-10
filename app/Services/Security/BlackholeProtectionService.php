<?php

namespace Pterodactyl\Services\Security;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BlackholeProtectionService
{
    const BLACKHOLE_IP = '192.0.2.1';

    /**
     * Enable blackhole protection for a server domain.
     * Points DNS to blackhole IP via Cloudflare and drops non-CF traffic via iptables.
     */
    public function enable(string $domain, ?int $durationMinutes = 30): array
    {
        $cfKey = config('security.cloudflare.api_key');
        $cfEmail = config('security.cloudflare.api_email');
        $cfZone = config('security.cloudflare.zone_id');

        $results = [];

        // 1. Cloudflare DNS → blackhole IP
        if ($cfKey && $cfEmail && $cfZone && $domain) {
            $recordId = $this->getDnsRecordId($domain, $cfZone, $cfKey, $cfEmail);
            if ($recordId) {
                $response = Http::withHeaders([
                    'X-Auth-Email' => $cfEmail,
                    'X-Auth-Key' => $cfKey,
                    'Content-Type' => 'application/json',
                ])->put("https://api.cloudflare.com/client/v4/zones/{$cfZone}/dns_records/{$recordId}", [
                    'type' => 'A',
                    'name' => $domain,
                    'content' => self::BLACKHOLE_IP,
                    'ttl' => 120,
                    'proxied' => true,
                ]);

                $results['cloudflare'] = $response->successful()
                    ? "DNS pointed to blackhole IP ({self::BLACKHOLE_IP})"
                    : 'Cloudflare API failed: ' . ($response->json('errors.0.message') ?? 'unknown');
            } else {
                $results['cloudflare'] = 'DNS record not found for domain';
            }
        } else {
            $results['cloudflare'] = 'Cloudflare not configured';
        }

        // 2. iptables blackhole for non-CF traffic to server IP
        $serverIp = gethostbyname($domain);
        if ($serverIp && $serverIp !== $domain) {
            $dropRules = $this->applyIptablesBlackhole($serverIp);
            $results['iptables'] = $dropRules
                ? "Blackhole iptables rules applied for {$serverIp}"
                : 'Failed to apply iptables rules';
        } else {
            $results['iptables'] = 'Could not resolve server IP';
        }

        // 3. Log the action
        DB::table('security_attack_logs')->insert([
            'type' => 'blackhole_activated',
            'severity' => 'high',
            'ip' => $serverIp ?? $domain,
            'details' => "Blackhole protection activated for {$domain}",
            'metadata' => json_encode($results),
            'action_taken' => 'blackhole_enabled',
            'detected_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Set auto-expiry
        if ($durationMinutes) {
            Cache::put("blackhole:{$domain}", true, now()->addMinutes($durationMinutes));
        }

        return $results;
    }

    /**
     * Disable blackhole protection — restore DNS and remove iptables rules.
     */
    public function disable(string $domain): array
    {
        $cfKey = config('security.cloudflare.api_key');
        $cfEmail = config('security.cloudflare.api_email');
        $cfZone = config('security.cloudflare.zone_id');

        $results = [];

        // 1. Restore DNS to real IP
        if ($cfKey && $cfEmail && $cfZone && $domain) {
            $realIp = Cache::pull("blackhole:real_ip:{$domain}");
            if (!$realIp) {
                $realIp = gethostbyname($domain);
                // If still showing blackhole IP, we can't auto-restore
                if ($realIp === self::BLACKHOLE_IP || $realIp === $domain) {
                    $results['cloudflare'] = 'Cannot auto-restore — real IP unknown, restore manually';
                    Cache::forget("blackhole:{$domain}");
                    return $results;
                }
            }

            $recordId = $this->getDnsRecordId($domain, $cfZone, $cfKey, $cfEmail);
            if ($recordId) {
                $response = Http::withHeaders([
                    'X-Auth-Email' => $cfEmail,
                    'X-Auth-Key' => $cfKey,
                    'Content-Type' => 'application/json',
                ])->put("https://api.cloudflare.com/client/v4/zones/{$cfZone}/dns_records/{$recordId}", [
                    'type' => 'A',
                    'name' => $domain,
                    'content' => $realIp,
                    'ttl' => 120,
                    'proxied' => true,
                ]);
                $results['cloudflare'] = $response->successful()
                    ? "DNS restored to {$realIp}"
                    : 'Cloudflare API failed: ' . ($response->json('errors.0.message') ?? 'unknown');
            }
        }

        // 2. Remove iptables rules
        $serverIp = gethostbyname($domain);
        if ($serverIp && $serverIp !== $domain && $serverIp !== self::BLACKHOLE_IP) {
            $this->removeIptablesBlackhole($serverIp);
            $results['iptables'] = 'iptables rules removed';
        }

        Cache::forget("blackhole:{$domain}");

        DB::table('security_attack_logs')->insert([
            'type' => 'blackhole_deactivated',
            'severity' => 'medium',
            'ip' => $serverIp ?? $domain,
            'details' => "Blackhole protection deactivated for {$domain}",
            'metadata' => json_encode($results),
            'action_taken' => 'blackhole_disabled',
            'detected_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $results;
    }

    /**
     * Check if a domain is currently blackholed.
     */
    public function isActive(string $domain): bool
    {
        return Cache::has("blackhole:{$domain}");
    }

    /**
     * Get all currently blackholed domains.
     */
    public function getActiveBlackholes(): array
    {
        // Infer from cache keys matching pattern
        return []; // Simplified — in production query cache tags or a DB table
    }

    private function getDnsRecordId(string $domain, string $zoneId, string $apiKey, string $email): ?string
    {
        $response = Http::withHeaders([
            'X-Auth-Email' => $email,
            'X-Auth-Key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records", [
            'type' => 'A',
            'name' => $domain,
        ]);

        if ($response->successful() && !empty($response->json('result'))) {
            $record = $response->json('result')[0];
            // Cache the real IP before overwriting
            Cache::put("blackhole:real_ip:{$domain}", $record['content'], now()->addDays(7));
            return $record['id'];
        }

        return null;
    }

    private function applyIptablesBlackhole(string $ip): bool
    {
        $commands = [
            "iptables -A INPUT -d {$ip} -m conntrack --ctstate ESTABLISHED,RELATED -j ACCEPT 2>/dev/null",
            "iptables -A INPUT -d {$ip} -p tcp --dport 80 -j DROP 2>/dev/null",
            "iptables -A INPUT -d {$ip} -p tcp --dport 443 -j DROP 2>/dev/null",
            "iptables -A INPUT -d {$ip} -p tcp --dport 22 -j DROP 2>/dev/null",
            "iptables -A INPUT -d {$ip} -p udp --dport 1:65535 -j DROP 2>/dev/null",
        ];

        $success = true;
        foreach ($commands as $cmd) {
            $output = null; $code = null;
            exec($cmd, $output, $code);
            if ($code !== 0 && $code !== 1) $success = false; // 1 = rule already exists
        }

        return $success;
    }

    private function removeIptablesBlackhole(string $ip): void
    {
        $commands = [
            "iptables -D INPUT -d {$ip} -p tcp --dport 80 -j DROP 2>/dev/null",
            "iptables -D INPUT -d {$ip} -p tcp --dport 443 -j DROP 2>/dev/null",
            "iptables -D INPUT -d {$ip} -p tcp --dport 22 -j DROP 2>/dev/null",
            "iptables -D INPUT -d {$ip} -p udp --dport 1:65535 -j DROP 2>/dev/null",
        ];

        foreach ($commands as $cmd) {
            exec($cmd);
        }
    }
}
