<?php

namespace RoyalPanel\Services\Security;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class IpBlockService
{
    public function isBlocked(string $ip): bool
    {
        return DB::table('security_blocked_ips')
            ->where('ip', $ip)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function blockIp(string $ip, string $reason = 'manual', string $type = 'manual', ?int $durationMinutes = null): void
    {
        DB::table('security_blocked_ips')->updateOrInsert(
            ['ip' => $ip],
            [
                'reason' => $reason,
                'type' => $type,
                'expires_at' => $durationMinutes ? now()->addMinutes($durationMinutes) : null,
                'updated_at' => now(),
            ]
        );
    }

    public function unblockIp(string $ip): void
    {
        DB::table('security_blocked_ips')->where('ip', $ip)->delete();
    }

    public function getBlockedIps(int $page = 1, int $perPage = 50): array
    {
        $query = DB::table('security_blocked_ips')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get()->toArray();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => ceil($total / $perPage),
        ];
    }

    public function getAllActiveBlocks(): array
    {
        return DB::table('security_blocked_ips')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->toArray();
    }

    public function isCountryBlocked(string $ip): bool
    {
        if (!config('security.ip_blocking.country_block.enabled')) return false;
        $mode = config('security.ip_blocking.country_block.mode', 'block');
        $countryCode = $this->getCountryCode($ip);
        if (!$countryCode) return false;
        if ($mode === 'block') {
            return in_array($countryCode, config('security.ip_blocking.country_block.blocked_countries', []));
        }
        return !in_array($countryCode, config('security.ip_blocking.country_block.allowed_countries', []));
    }

    private function getCountryCode(string $ip): ?string
    {
        try {
            $response = Http::get("http://ip-api.com/json/{$ip}?fields=countryCode");
            $data = $response->json();
            return $data['countryCode'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function cleanupExpired(): void
    {
        DB::table('security_blocked_ips')->where('expires_at', '<', now())->delete();
    }

    public function getTotalBlocked(): int
    {
        return DB::table('security_blocked_ips')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->count();
    }
}
