<?php

namespace RoyalPanel\Services\Security;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class IpGeoService
{
    public function lookup(string $ip): ?array
    {
        $cached = DB::table('ip_geo_cache')->where('ip', $ip)->first();
        if ($cached) {
            return [
                'country_code' => $cached->country_code,
                'country_name' => $cached->country_name,
            ];
        }

        $data = $this->fetch($ip);
        if (!$data) return null;

        DB::table('ip_geo_cache')->insert([
            'ip' => $ip,
            'country_code' => $data['country_code'] ?? null,
            'country_name' => $data['country_name'] ?? null,
            'cached_at' => Carbon::now(),
        ]);

        return $data;
    }

    public function batchTopCountries(int $limit = 10): array
    {
        $ips = DB::table('activity_logs')
            ->select('ip')
            ->selectRaw('COUNT(*) as count')
            ->whereNotNull('ip')
            ->where('ip', '!=', '')
            ->where('timestamp', '>=', Carbon::now()->subDays(30))
            ->groupBy('ip')
            ->orderByDesc('count')
            ->limit(100)
            ->get()
            ->pluck('count', 'ip')
            ->toArray();

        $result = [];
        foreach ($ips as $ip => $count) {
            $geo = $this->lookup($ip);
            if ($geo && $geo['country_code']) {
                $code = strtolower($geo['country_code']);
                if (!isset($result[$code])) {
                    $result[$code] = ['country' => $geo['country_name'], 'code' => $geo['country_code'], 'count' => 0];
                }
                $result[$code]['count'] += $count;
            }
        }

        usort($result, fn($a, $b) => $b['count'] <=> $a['count']);
        return array_slice($result, 0, $limit);
    }

    private function fetch(string $ip): ?array
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
            return ['country_code' => 'local', 'country_name' => 'Local'];
        }

        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode");
            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'country_code' => $response->json('countryCode'),
                    'country_name' => $response->json('country'),
                ];
            }
        } catch (\Exception $e) {
            // silently fail
        }

        return null;
    }
}
