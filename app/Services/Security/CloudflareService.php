<?php

namespace Pterodactyl\Services\Security;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    private ?string $apiToken;
    private ?string $zoneId;
    private string $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('security.cloudflare.api_token');
        $this->zoneId = config('security.cloudflare.zone_id');
    }

    public function enabled(): bool
    {
        return config('security.cloudflare.enabled', false)
            && !empty($this->apiToken)
            && !empty($this->zoneId);
    }

    public function setSecurityLevel(string $level): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PUT', "/zones/{$this->zoneId}/settings/security_level", ['value' => $level]);
    }

    public function setUnderAttackMode(bool $enabled): array
    {
        return $this->setSecurityLevel($enabled ? 'under_attack' : 'medium');
    }

    public function setBotFightMode(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/bot_management", ['fight_mode' => $enabled]);
    }

    public function setBrowserIntegrityCheck(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/browser_check", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setChallengePassage(int $seconds): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/challenge_ttl", ['value' => $seconds]);
    }

    public function setUrlNormalization(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/url_normalization", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setPrivacyPass(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/privacy_pass", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setIpGeolocation(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/ip_geolocation", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setTrueClientIp(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/true_client_ip_header", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setSsl(string $mode): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/ssl", ['value' => $mode]);
    }

    public function setAlwaysUseHttps(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/always_use_https", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setMinimumTlsVersion(string $version): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/min_tls_version", ['value' => $version]);
    }

    public function setOpportunisticEncryption(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/opportunistic_encryption", ['value' => $enabled ? 'on' : 'off']);
    }

    public function addWafRule(string $expression, string $action, string $description): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('POST', "/zones/{$this->zoneId}/rulesets/phases/http_request_firewall_custom/entrypoint", [
            'description' => $description, 'expression' => $expression, 'action' => $action, 'enabled' => true,
        ]);
    }

    public function setRateLimitingRule(string $pattern, int $maxRequests, int $period, string $action): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('POST', "/zones/{$this->zoneId}/rate_limits", [
            'description' => "Rate limit: {$pattern}",
            'match' => ['request' => ['url' => $pattern]],
            'threshold' => $maxRequests, 'period' => $period,
            'action' => ['mode' => $action, 'timeout' => 60],
        ]);
    }

    public function addOwaspRules(): array
    {
        $rules = [
            ['expression' => '(http.request.uri.path contains "/artisan" or http.request.uri.path contains ".env" or http.request.uri.path contains "/wp-admin")', 'action' => 'block', 'description' => 'Block exploit paths'],
            ['expression' => '(http.request.method in {"GET" "POST"} and cf.threat_score > 50)', 'action' => 'challenge', 'description' => 'Challenge high-threat visitors'],
            ['expression' => '(http.request.uri.query contains "<script>" or http.request.body contains "UNION+SELECT")', 'action' => 'block', 'description' => 'Block XSS & SQLi'],
            ['expression' => '(http.request.uri.path contains "../" or http.request.uri.path contains "%2e%2e")', 'action' => 'block', 'description' => 'Block path traversal'],
        ];
        $results = [];
        foreach ($rules as $rule) {
            $results[] = $this->addWafRule($rule['expression'], $rule['action'], $rule['description']);
        }
        return $results;
    }

    public function applyRecommendedSsl(): array
    {
        return [
            'full_ssl' => $this->setSsl('full'),
            'always_https' => $this->setAlwaysUseHttps(true),
            'min_tls' => $this->setMinimumTlsVersion('1.2'),
            'opp_encryption' => $this->setOpportunisticEncryption(true),
        ];
    }

    public function enableLockdownMode(): array
    {
        return [
            'under_attack' => $this->setUnderAttackMode(true),
            'browser_check' => $this->setBrowserIntegrityCheck(true),
            'challenge_ttl' => $this->setChallengePassage(300),
            'bot_fight' => $this->setBotFightMode(true),
            'security_level' => $this->setSecurityLevel('high'),
        ];
    }

    public function disableLockdownMode(): array
    {
        return [
            'under_attack' => $this->setUnderAttackMode(false),
            'browser_check' => $this->setBrowserIntegrityCheck(false),
            'challenge_ttl' => $this->setChallengePassage(1800),
            'bot_fight' => $this->setBotFightMode(false),
            'security_level' => $this->setSecurityLevel('medium'),
        ];
    }

    public function blockIp(string $ip, string $note = ''): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('POST', "/zones/{$this->zoneId}/firewall/access_rules/rules", [
            'mode' => 'block',
            'configuration' => ['target' => 'ip', 'value' => $ip],
            'notes' => $note ?: 'Blocked by Security',
        ]);
    }

    public function getSslMode(): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('GET', "/zones/{$this->zoneId}/settings/ssl");
    }

    public function getDnsRecords(string $type = null): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        $params = $type ? ['type' => $type] : [];
        return $this->request('GET', "/zones/{$this->zoneId}/dns_records", $params);
    }

    public function getDnsProxyStatus(string $name): array
    {
        $records = $this->getDnsRecords();
        if (!($records['success'] ?? false)) return ['proxied' => false];
        foreach ($records['result'] ?? [] as $r) {
            if (($r['name'] ?? '') === $name) {
                return ['proxied' => ($r['proxied'] ?? false), 'content' => $r['content'] ?? ''];
            }
        }
        return ['proxied' => false];
    }

    public function setDnsProxy(string $name, bool $proxied): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        $records = $this->getDnsRecords();
        if (!($records['success'] ?? false)) return $records;
        foreach ($records['result'] ?? [] as $r) {
            if (($r['name'] ?? '') === $name || ($r['name'] ?? '') === $name . '.') {
                return $this->request('PATCH', "/zones/{$this->zoneId}/dns_records/{$r['id']}", ['proxied' => $proxied]);
            }
        }
        return ['success' => false, 'error' => "DNS record '{$name}' not found"];
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiToken}", 'Content-Type' => 'application/json',
            ])->{$method}($this->baseUrl . $endpoint, $data);
            $body = $response->json();
            if (!$response->successful()) {
                Log::warning("Cloudflare API error: {$response->status()} - " . ($body['errors'][0]['message'] ?? 'Unknown'));
            }
            return ['success' => $response->successful(), 'result' => $body, 'status' => $response->status()];
        } catch (\Exception $e) {
            Log::error("Cloudflare API exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function notConfigured(): array
    {
        return ['success' => false, 'error' => 'Cloudflare not configured'];
    }
}
