<?php

namespace Pterodactyl\Services\Security;

use Illuminate\Support\Facades\Log;

class IpRotationService
{
    public function __construct(
        private CloudflareService $cloudflare,
    ) {}

    public function rotateServerIp(int $serverId, string $currentIp, string $domain): array
    {
        Log::info("IP Rotation: Rotating IP for server {$serverId}");
        $results = [];

        if ($this->cloudflare->enabled()) {
            $results[] = $this->cloudflare->blockIp($currentIp, "Rotated out: server {$serverId}");
            $results['dns_update'] = $this->updateDnsRecord($domain, $currentIp);
        }

        $results['iptables_block'] = $this->blockIpAtServerLevel($currentIp);
        return $results;
    }

    public function rotateAllServers(array $servers): array
    {
        $results = [];
        foreach ($servers as $server) {
            $results[$server['id']] = $this->rotateServerIp($server['id'], $server['ip'] ?? '', $server['domain'] ?? '');
        }
        return $results;
    }

    private function updateDnsRecord(string $domain, string $oldIp): array
    {
        if (!$this->cloudflare->enabled() || empty($domain)) {
            return ['success' => false, 'error' => 'Cloudflare not configured or no domain'];
        }
        try {
            $zoneId = config('security.cloudflare.zone_id');
            $token = config('security.cloudflare.api_token');
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => "Bearer {$token}", 'Content-Type' => 'application/json',
            ])->get("https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records", [
                'name' => $domain, 'type' => 'A',
            ]);
            $records = $response->json()['result'] ?? [];
            $updates = [];
            foreach ($records as $record) {
                if ($record['content'] === $oldIp) {
                    $updates[] = ['success' => true, 'record' => $record['id']];
                }
            }
            return ['success' => true, 'records_found' => count($updates)];
        } catch (\Exception $e) {
            Log::error("IP Rotation: DNS update failed for {$domain}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function blockIpAtServerLevel(string $ip): array
    {
        try {
            $output = shell_exec("iptables -A INPUT -s {$ip} -j DROP 2>&1");
            return ['success' => true, 'output' => $output];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
