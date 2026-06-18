<?php

namespace RoyalPanel\Services\Security;

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
        // Try old rate limit API first, fall back to new ruleset-based API
        $result = $this->request('POST', "/zones/{$this->zoneId}/rate_limits", [
            'description' => "Rate limit: {$pattern}",
            'match' => ['request' => ['url' => $pattern]],
            'threshold' => $maxRequests, 'period' => $period,
            'action' => ['mode' => $action, 'timeout' => 60],
        ]);
        if ($result['success'] ?? false) return $result;
        // If old API is in maintenance mode, try new ruleset-based rate limiting API
        $errors = $result['result']['errors'] ?? [];
        $isMaintenance = false;
        foreach ($errors as $e) {
            if (isset($e['code']) && in_array($e['code'], [10037, 10042])) { $isMaintenance = true; break; }
        }
        if (!$isMaintenance) return $result;
        // New rate limiting via rulesets
        $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $pattern);
        $expression = '(http.request.uri.path ~ "' . $escaped . '")';
        return $this->request('POST', "/zones/{$this->zoneId}/rulesets/phases/http_ratelimit/entrypoint", [
            'description' => "Rate limit: {$pattern}",
            'kind' => 'zone', 'phase' => 'http_ratelimit',
            'rules' => [[
                'description' => "Rate limit: {$pattern}",
                'expression' => $expression,
                'action' => $action,
                'ratelimit' => [
                    'characteristics' => ['cf.client.ip'],
                    'period' => $period,
                    'requests_per_period' => $maxRequests,
                    'requests_to_origin' => false,
                    'mitigation_timeout' => 60,
                ],
            ]],
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

    public function getSettings(): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        $settings = [];
        $endpoints = [
            'ssl' => '/settings/ssl',
            'security_level' => '/settings/security_level',
            'browser_check' => '/settings/browser_check',
            'challenge_ttl' => '/settings/challenge_ttl',
            'always_use_https' => '/settings/always_use_https',
            'min_tls_version' => '/settings/min_tls_version',
            'opportunistic_encryption' => '/settings/opportunistic_encryption',
            'ip_geolocation' => '/settings/ip_geolocation',
            'privacy_pass' => '/settings/privacy_pass',
            'development_mode' => '/settings/development_mode',
            'cache_level' => '/settings/cache_level',
            'always_online' => '/settings/always_online',
            'rocket_loader' => '/settings/rocket_loader',
            'minify' => '/settings/minify',
        ];
        $atLeastOne = false;
        foreach ($endpoints as $key => $path) {
            $resp = $this->request('GET', "/zones/{$this->zoneId}{$path}");
            if ($resp['success'] ?? false) {
                $atLeastOne = true;
                $val = $resp['result']['result']['value'] ?? null;
                if ($key === 'minify' && is_array($val)) {
                    $settings['minify_js'] = $val['js'] ?? 'off';
                    $settings['minify_css'] = $val['css'] ?? 'off';
                    $settings['minify_html'] = $val['html'] ?? 'off';
                } else {
                    $settings[$key] = $val;
                }
            }
        }
        $botResp = $this->request('GET', "/zones/{$this->zoneId}/bot_management");
        if ($botResp['success'] ?? false) {
            $atLeastOne = true;
            $settings['bot_fight_mode'] = $botResp['result']['result']['fight_mode'] ?? null;
        }
        return ['success' => $atLeastOne, 'settings' => $settings];
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

    public function setDevelopmentMode(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/development_mode", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setCacheLevel(string $level): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/cache_level", ['value' => $level]);
    }

    public function setAlwaysOnline(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/always_online", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setRocketLoader(bool $enabled): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/rocket_loader", ['value' => $enabled ? 'on' : 'off']);
    }

    public function setAutoMinify(array $extensions): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        $value = ['js' => in_array('js', $extensions) ? 'on' : 'off', 'css' => in_array('css', $extensions) ? 'on' : 'off', 'html' => in_array('html', $extensions) ? 'on' : 'off'];
        return $this->request('PATCH', "/zones/{$this->zoneId}/settings/minify", ['value' => $value]);
    }

    public function getDnsRecordsWithProxy(): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        $resp = $this->request('GET', "/zones/{$this->zoneId}/dns_records?per_page=100");
        if (!($resp['success'] ?? false)) return $resp;
        $records = [];
        foreach ($resp['result']['result'] ?? [] as $r) {
            $records[] = ['id' => $r['id'], 'name' => $r['name'], 'type' => $r['type'], 'content' => $r['content'], 'proxied' => $r['proxied'] ?? false, 'ttl' => $r['ttl'] ?? 1];
        }
        return ['success' => true, 'records' => $records];
    }

    public function createDnsRecord(string $name, string $type, string $content, int $ttl = 1, bool $proxied = false): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('POST', "/zones/{$this->zoneId}/dns_records", [
            'name' => $name, 'type' => $type, 'content' => $content,
            'ttl' => $ttl, 'proxied' => $proxied,
        ]);
    }

    public function updateDnsRecord(string $recordId, array $data): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/dns_records/{$recordId}", $data);
    }

    public function deleteDnsRecord(string $recordId): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('DELETE', "/zones/{$this->zoneId}/dns_records/{$recordId}");
    }

    public function deleteRateLimit(string $ruleId): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('DELETE', "/zones/{$this->zoneId}/rate_limits/{$ruleId}");
    }

    public function deleteWafRule(string $ruleId, string $rulesetId): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('DELETE', "/zones/{$this->zoneId}/rulesets/{$rulesetId}/rules/{$ruleId}");
    }

    public function getCustomRuleset(): ?string
    {
        if (!$this->enabled()) return null;
        $resp = $this->request('GET', "/zones/{$this->zoneId}/rulesets/phases/http_request_firewall_custom/entrypoint");
        if (!($resp['success'] ?? false)) return null;
        return $resp['result']['result']['id'] ?? null;
    }

    public function addRuleToCustomRuleset(string $rulesetId, string $expression, string $action, string $description): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('POST', "/zones/{$this->zoneId}/rulesets/{$rulesetId}/rules", [
            'description' => $description, 'expression' => $expression, 'action' => $action, 'enabled' => true,
        ]);
    }

        public function toggleDnsProxy(string $recordId, bool $proxied): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        return $this->request('PATCH', "/zones/{$this->zoneId}/dns_records/{$recordId}", ['proxied' => $proxied]);
    }

    public function getWafRules(): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        $resp = $this->request('GET', "/zones/{$this->zoneId}/rulesets");
        if (!($resp['success'] ?? false)) return $resp;
        return ['success' => true, 'rulesets' => $resp['result']['result'] ?? []];
    }

    public function getRateLimits(): array
    {
        if (!$this->enabled()) return $this->notConfigured();
        $resp = $this->request('GET', "/zones/{$this->zoneId}/rate_limits?per_page=50");
        if (!($resp['success'] ?? false)) return $resp;
        return ['success' => true, 'rules' => $resp['result']['result'] ?? []];
    }

    private function notConfigured(): array
    {
        return ['success' => false, 'error' => 'Cloudflare not configured'];
    }
}
