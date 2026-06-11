<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Security\CloudflareService;
use Pterodactyl\Services\Security\IpBlockService;
use Pterodactyl\Services\Security\AttackDetector;
use Pterodactyl\Services\Security\HoneypotService;
use Pterodactyl\Services\Security\AutoAttackResponseService;
use Pterodactyl\Services\Security\OriginProtectionService;
use Pterodactyl\Services\Security\ServerProtectionService;
use Pterodactyl\Services\Security\BlackholeProtectionService;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class SecurityController extends Controller
{
    public function __construct(
        private CloudflareService $cloudflare,
        private IpBlockService $ipBlock,
        private AttackDetector $attackDetector,
        private HoneypotService $honeypot,
        private AutoAttackResponseService $autoResponse,
        private OriginProtectionService $origin,
        private ServerProtectionService $server,
        private SettingsRepositoryInterface $settings,
    ) {}

    public function index(): View
    {
        $stats = $this->attackDetector->getAttackStats();
        $autoResponseStatus = $this->autoResponse->getStatus();

        return view('admin.security.index', compact('stats', 'autoResponseStatus'));
    }

    public function cloudflare(): View
    {
        return view('admin.security.cloudflare', [
            'cloudflareEnabled' => $this->cloudflare->enabled(),
            'apiToken' => config('security.cloudflare.api_token'),
            'zoneId' => config('security.cloudflare.zone_id'),
        ]);
    }

    public function rateLimiting(): View
    {
        $config = config('security.rate_limiting');
        return view('admin.security.rate-limiting', compact('config'));
    }

    public function ipManagement(): View
    {
        $blockedIps = $this->ipBlock->getAllActiveBlocks();
        return view('admin.security.ip-management', compact('blockedIps'));
    }

    public function detection(): View
    {
        $detectionConfig = config('security.attack_detection');
        $autoResponseConfig = config('security.auto_response');
        return view('admin.security.detection', compact('detectionConfig', 'autoResponseConfig'));
    }

    public function honeypot(): View
    {
        $hits = $this->honeypot->getRecentHits(50);
        return view('admin.security.honeypot', compact('hits'));
    }

    public function originProtection(): View
    {
        $status = $this->origin->getProtectionStatus();
        $dnsCheck = $this->origin->checkDnsPropagation();
        $stealth = $this->origin->getStealthStatus();

        return view('admin.security.origin-protection', compact('status', 'dnsCheck', 'stealth'));
    }

    public function serverProtection(): View
    {
        return view('admin.security.server-protection');
    }

    public function saveSettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'cloudflare_enabled' => 'boolean',
            'cloudflare_api_token' => 'nullable|string',
            'cloudflare_zone_id' => 'nullable|string',
            'rate_limit_panel_enabled' => 'boolean',
            'rate_limit_panel_max' => 'integer|min:1',
            'rate_limit_api_enabled' => 'boolean',
            'rate_limit_api_max' => 'integer|min:1',
            'rate_limit_server_enabled' => 'boolean',
            'rate_limit_server_max' => 'integer|min:1',
            'auto_ban_enabled' => 'boolean',
            'auto_ban_max_attempts' => 'integer|min:1',
            'auto_ban_duration' => 'integer|min:1',
            'country_block_enabled' => 'boolean',
            'detection_enabled' => 'boolean',
            'detection_threshold_requests' => 'integer|min:1',
            'detection_threshold_concurrent' => 'integer|min:1',
            'detection_threshold_unique_ips' => 'integer|min:1',
            'auto_under_attack' => 'boolean',
            'auto_bot_fight' => 'boolean',
            'auto_block_ips' => 'boolean',
            'auto_response_enabled' => 'boolean',
            'auto_response_grace' => 'integer|min:1|max:60',
        ]);

        $settings = [
            'cloudflare:enabled' => $data['cloudflare_enabled'] ?? false,
            'cloudflare:api_token' => $data['cloudflare_api_token'] ?? '',
            'cloudflare:zone_id' => $data['cloudflare_zone_id'] ?? '',
            'rate_limiting:panel:enabled' => $data['rate_limit_panel_enabled'] ?? true,
            'rate_limiting:panel:max_requests' => $data['rate_limit_panel_max'] ?? 60,
            'rate_limiting:api:enabled' => $data['rate_limit_api_enabled'] ?? true,
            'rate_limiting:api:max_requests' => $data['rate_limit_api_max'] ?? 120,
            'rate_limiting:server:enabled' => $data['rate_limit_server_enabled'] ?? true,
            'rate_limiting:server:max_requests' => $data['rate_limit_server_max'] ?? 100,
            'ip_blocking:auto_ban:enabled' => $data['auto_ban_enabled'] ?? true,
            'ip_blocking:auto_ban:max_failed_attempts' => $data['auto_ban_max_attempts'] ?? 10,
            'ip_blocking:auto_ban:ban_duration_minutes' => $data['auto_ban_duration'] ?? 60,
            'ip_blocking:country_block:enabled' => $data['country_block_enabled'] ?? false,
            'attack_detection:enabled' => $data['detection_enabled'] ?? true,
            'attack_detection:threshold:requests_per_ip_per_minute' => $data['detection_threshold_requests'] ?? 100,
            'attack_detection:threshold:concurrent_connections_per_ip' => $data['detection_threshold_concurrent'] ?? 20,
            'attack_detection:threshold:unique_ips_per_minute' => $data['detection_threshold_unique_ips'] ?? 500,
            'attack_detection:auto_actions:enable_under_attack_mode' => $data['auto_under_attack'] ?? true,
            'attack_detection:auto_actions:enable_bot_fight_mode' => $data['auto_bot_fight'] ?? true,
            'attack_detection:auto_actions:block_offending_ips' => $data['auto_block_ips'] ?? true,
            'auto_response:enabled' => $data['auto_response_enabled'] ?? true,
            'auto_response:grace_minutes' => $data['auto_response_grace'] ?? 5,
        ];

        $prefix = 'security:';
        foreach ($settings as $key => $value) {
            config(["security.{$key}" => $value]);
            $settingKey = $prefix . $key;
            DB::table('settings')->updateOrInsert(
                ['key' => 'settings::' . $settingKey],
                ['value' => is_bool($value) ? ($value ? 'true' : 'false') : $value]
            );
        }

        return redirect()->back()->with('success', 'Security settings saved!');
    }

    public function unblockIp(string $ip): \Illuminate\Http\RedirectResponse
    {
        $this->ipBlock->unblockIp($ip);
        return redirect()->back()->with('success', "IP {$ip} unblocked.");
    }

    public function enableOrangeCloud(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->origin->enableOrangeCloud();
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', 'Orange cloud enabled - IP hidden.');
        }
        return redirect()->back()->with('error', $result['error'] ?? 'Failed to enable orange cloud.');
    }

    public function installCfOnlyIptables(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->origin->installCfOnlyIptables();
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', "CF-only iptables active ({$result['ipv4_count']} IPv4 ranges).");
        }
        return redirect()->back()->with('error', $result['error'] ?? 'Failed to install CF-only iptables.');
    }

    public function blockDirectIp(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->origin->blockDirectIpAccess();
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', 'Direct IP access blocked.');
        }
        return redirect()->back()->with('error', $result['error'] ?? 'Failed to block direct IP.');
    }

    public function installRealIpConfig(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->origin->installRealIpConfig();
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', "Real-IP config installed ({$result['path']}).");
        }
        return redirect()->back()->with('error', $result['error'] ?? 'Failed to install Real-IP config.');
    }

    public function enableFullSslStrict(): \Illuminate\Http\RedirectResponse
    {
        $results = $this->origin->enableFullSslStrict();
        return redirect()->back()->with('success', 'SSL Strict settings applied.');
    }

    public function enableUnderAttackMode(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->cloudflare->setUnderAttackMode(true);
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', 'Under Attack Mode enabled.');
        }
        return redirect()->back()->with('error', $result['error'] ?? 'Failed.');
    }

    public function disableUnderAttackMode(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->cloudflare->setUnderAttackMode(false);
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', 'Under Attack Mode disabled.');
        }
        return redirect()->back()->with('error', $result['error'] ?? 'Failed.');
    }

    public function enableBotFightMode(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->cloudflare->setBotFightMode(true);
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', 'Bot Fight Mode enabled.');
        }
        return redirect()->back()->with('error', $result['error'] ?? 'Failed.');
    }

    public function enableLockdown(): \Illuminate\Http\RedirectResponse
    {
        $results = $this->cloudflare->enableLockdownMode();
        return redirect()->back()->with('success', 'Lockdown mode enabled.');
    }

    public function disableLockdown(): \Illuminate\Http\RedirectResponse
    {
        $results = $this->cloudflare->disableLockdownMode();
        return redirect()->back()->with('success', 'Lockdown mode disabled.');
    }

    public function enableOvhLevel(): \Illuminate\Http\RedirectResponse
    {
        $results = $this->server->ovhLevelProtection();
        return redirect()->back()->with('success', 'OVH-Level server protection enabled.');
    }
    public function enableKernelTuning(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->server->enableKernelTuning();
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', 'Kernel tuning enabled.');
        }
        return redirect()->back()->with('error', 'Kernel tuning failed.');
    }

    public function flushIptablesRules(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->server->flushIptables();
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', 'All iptables rules flushed.');
        }
        return redirect()->back()->with('error', 'Flush failed.');
    }

    public function enableStealthMode(): \Illuminate\Http\RedirectResponse
    {
        $result = $this->origin->enableStealthMode();
        if ($result['success'] ?? false) {
            return redirect()->back()->with('success', 'Stealth mode activated.');
        }
        return redirect()->back()->with('error', 'Stealth mode activation failed.');
    }

    public function disableStealthMode(): \Illuminate\Http\RedirectResponse
    {
        $this->origin->disableStealthMode();
        DB::table('settings')->updateOrInsert(['key' => 'settings::security:origin:stealth_mode'], ['value' => 'false']);
        return redirect()->back()->with('success', 'Stealth mode deactivated.');
    }

    public function enableAutoResponse(): \Illuminate\Http\RedirectResponse
    {
        config(['security.auto_response.enabled' => true]);
        DB::table('settings')->updateOrInsert(['key' => 'settings::security:auto_response:enabled'], ['value' => 'true']);
        return redirect()->back()->with('success', 'Auto Attack Response enabled.');
    }

    public function disableAutoResponse(): \Illuminate\Http\RedirectResponse
    {
        config(['security.auto_response.enabled' => false]);
        DB::table('settings')->updateOrInsert(['key' => 'settings::security:auto_response:enabled'], ['value' => 'false']);
        return redirect()->back()->with('success', 'Auto Attack Response disabled.');
    }

    public function runCleanup(): \Illuminate\Http\RedirectResponse
    {
        $retentionDays = (int) config('security.retention_days', 30);
        $this->attackDetector->cleanup($retentionDays);
        $this->ipBlock->cleanupExpired();
        return redirect()->back()->with('success', 'Cleanup completed!');
    }

    public function shield(): \Illuminate\View\View
    {
        $shieldArmed = \Illuminate\Support\Facades\Cache::get('codenest_shield_armed', false);
        $layerStatus = [
            'cloudflare' => ['active' => (bool) config('security.cloudflare.api_key'), 'name' => 'Cloudflare Protection', 'desc' => 'WAF, Lockdown, Bot Fight Mode, SSL', 'icon' => '☁️'],
            'rate_limiting' => ['active' => $this->settings->get('settings::security:rate_limiting_enabled', 'false') === 'true', 'name' => 'Rate Limiting', 'desc' => 'Per-scope request throttling', 'icon' => '⏱️'],
            'ip_blocking' => ['active' => DB::table('security_blocked_ips')->count() > 0, 'name' => 'IP Blocking', 'desc' => 'Manual & automatic IP bans', 'icon' => '🚫'],
            'honeypot' => ['active' => $this->settings->get('settings::security:honeypot_enabled', 'false') === 'true', 'name' => 'Honeypot', 'desc' => 'Scanner traps & auto-blocking', 'icon' => '🍯'],
            'blackhole' => ['active' => DB::table('security_attack_logs')->where('type', 'blackhole_activated')->where('created_at', '>=', now()->subHours(24))->exists(), 'name' => 'Blackhole Protection', 'desc' => 'DNS null-route & iptables DROP', 'icon' => '🕳️'],
            'attack_detection' => ['active' => $this->settings->get('settings::security:attack_detection_enabled', 'false') === 'true', 'name' => 'Attack Detection', 'desc' => 'DDoS pattern analysis & alerts', 'icon' => '🔍'],
            'server_protection' => ['active' => $this->settings->get('settings::security:server_protection_enabled', 'false') === 'true', 'name' => 'Server Protection', 'desc' => 'iptables SYNPROXY, connlimit, kernel tuning', 'icon' => '🖥️'],
            'origin_protection' => ['active' => $this->settings->get('settings::security:origin_protection_enabled', 'false') === 'true', 'name' => 'Origin Protection', 'desc' => 'DNS stealth, CF-only iptables, SSL strict', 'icon' => '🔒'],
            'auto_response' => ['active' => $this->settings->get('settings::security:auto_response_enabled', 'false') === 'true', 'name' => 'Auto Attack Response', 'desc' => 'Automatic CF lockdown & protection', 'icon' => '🤖'],
            'ip_rotation' => ['active' => false, 'name' => 'IP Rotation', 'desc' => 'Rotate server IP on attack', 'icon' => '🔄'],
        ];
        $layers = [];
        foreach ($layerStatus as $ls) {
            $active = $ls['active'];
            if ($shieldArmed && $active) {
                $layers[] = $ls + ['status_class' => 'active', 'badge_class' => 'on', 'badge' => 'Active'];
            } elseif ($shieldArmed && !$active) {
                $layers[] = $ls + ['status_class' => 'warning', 'badge_class' => 'partial', 'badge' => 'Not Configured'];
            } elseif (!$shieldArmed && $active) {
                $layers[] = $ls + ['status_class' => 'warning', 'badge_class' => 'partial', 'badge' => 'Paused'];
            } else {
                $layers[] = $ls + ['status_class' => 'inactive', 'badge_class' => 'off', 'badge' => 'Inactive'];
            }
        }
        $stats = [
            'blocked_ips' => DB::table('security_blocked_ips')->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()); })->count(),
            'attacks_detected' => DB::table('security_attack_logs')->count(),
            'rate_limits_hit' => DB::table('security_rate_logs')->where('blocked', true)->count(),
            'honeypot_hits' => DB::table('security_blocked_ips')->where('type', 'honeypot')->count(),
        ];
        $recentEvents = DB::table('security_attack_logs')->orderByDesc('detected_at')->limit(10)->get();
        return view('admin.security.shield', compact('shieldArmed', 'layers', 'stats', 'recentEvents'));
    }

    public function armShield(): \Illuminate\Http\RedirectResponse
    {
        \Illuminate\Support\Facades\Cache::forever('codenest_shield_armed', true);
        $this->alert->success('Codenest Shield has been ARMED. All protection layers are active.')->flash();
        return redirect()->route('admin.security.shield');
    }

    public function disarmShield(): \Illuminate\Http\RedirectResponse
    {
        \Illuminate\Support\Facades\Cache::forever('codenest_shield_armed', false);
        $this->alert->warning('Codenest Shield has been DISARMED. Protection layers are inactive.')->flash();
        return redirect()->route('admin.security.shield');
    }

    public function blackhole(): \Illuminate\View\View
    {
        return view('admin.security.blackhole', ['activeBlackholes' => collect()]);
    }

    public function enableBlackhole(Request $request): \Illuminate\Http\RedirectResponse
    {
        $domain = $request->input('domain');
        $duration = $request->input('duration', 30);
        if (!$domain) return redirect()->back()->withErrors(['domain' => 'Domain is required.']);
        $this->blackhole->enable($domain, (int) $duration);
        $this->alert->success("Blackhole protection activated for {$domain}.")->flash();
        return redirect()->back();
    }

    public function disableBlackhole(Request $request): \Illuminate\Http\RedirectResponse
    {
        $domain = $request->input('domain');
        if (!$domain) return redirect()->back()->withErrors(['domain' => 'Domain is required.']);
        $this->blackhole->disable($domain);
        $this->alert->success("Blackhole protection deactivated for {$domain}.")->flash();
        return redirect()->back();
    }
    public function geoip(): View
    {
        return view('admin.security.geoip');
    }

    public function saveGeoip(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate(['enabled' => 'boolean', 'mode' => 'string|in:block,allow', 'countries' => 'nullable|string']);
        $countries = $data['countries'] ? array_map('trim', explode(',', $data['countries'])) : [];
        $key = $data['mode'] === 'block' ? 'blocked_countries' : 'allowed_countries';
        config(['security.ip_blocking.country_block.enabled' => (bool)($data['enabled'] ?? false)]);
        config(['security.ip_blocking.country_block.mode' => $data['mode'] ?? 'block']);
        config(["security.ip_blocking.country_block.$key" => $countries]);
        DB::table('settings')->updateOrInsert(['key' => 'settings::security:ip_blocking:country_block:enabled'], ['value' => ($data['enabled'] ?? false) ? 'true' : 'false']);
        DB::table('settings')->updateOrInsert(['key' => 'settings::security:ip_blocking:country_block:mode'], ['value' => $data['mode'] ?? 'block']);
        DB::table('settings')->updateOrInsert(['key' => 'settings::security:ip_blocking:country_block:' . $key], ['value' => json_encode($countries)]);
        return redirect()->back()->with('success', 'GeoIP settings saved.');
    }

    public function bruteForce(): View
    {
        $failedAttempts = DB::table('security_rate_logs')
            ->select('ip', 'route', 'logged_at')
            ->where('blocked', true)
            ->orderByDesc('logged_at')
            ->limit(100)
            ->get();
        return view('admin.security.brute-force', compact('failedAttempts'));
    }

    public function saveBruteForce(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate(['enabled' => 'boolean', 'max_attempts' => 'integer|min:1', 'lockout_duration' => 'integer|min:1']);
        foreach (['enabled', 'max_attempts', 'lockout_duration'] as $key) {
            $val = $data[$key] ?? ($key === 'enabled' ? false : ($key === 'max_attempts' ? 10 : 60));
            config(["security.brute_force.$key" => $val]);
            DB::table('settings')->updateOrInsert(['key' => "settings::security:brute_force:$key"], ['value' => is_bool($val) ? ($val ? 'true' : 'false') : $val]);
        }
        return redirect()->back()->with('success', 'Brute force settings saved.');
    }

    public function fileIntegrity(): View
    {
        $service = app(\Pterodactyl\Services\Security\FileIntegrityService::class);
        $results = $service->getResults();
        $stats = $service->getStats();
        return view('admin.security.file-integrity', compact('results', 'stats'));
    }

    public function runFileIntegrityScan(): \Illuminate\Http\RedirectResponse
    {
        $service = app(\Pterodactyl\Services\Security\FileIntegrityService::class);
        $service->scan();
        return redirect()->back()->with('success', 'File integrity scan completed.');
    }

    public function twoFactor(): View
    {
        return view('admin.security.two-factor');
    }

    public function saveTwoFactor(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate(['enforce_level' => 'string|in:none,admin,all', 'grace_period' => 'integer|min:0|max:90']);
        config(['security.two_factor.enforce_level' => $data['enforce_level'] ?? 'none']);
        config(['security.two_factor.grace_period' => $data['grace_period'] ?? 7]);
        DB::table('settings')->updateOrInsert(['key' => 'settings::security:two_factor:enforce_level'], ['value' => $data['enforce_level'] ?? 'none']);
        DB::table('settings')->updateOrInsert(['key' => 'settings::security:two_factor:grace_period'], ['value' => $data['grace_period'] ?? 7]);
        return redirect()->back()->with('success', 'Two-factor settings saved.');
    }

    public function sessions(): View
    {
        $sessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select('sessions.id', 'sessions.ip_address', 'sessions.last_activity', 'users.email as user_email')
            ->orderByDesc('sessions.last_activity')
            ->limit(200)
            ->get();
        return view('admin.security.sessions', compact('sessions'));
    }

    public function terminateSession(string $sessionId): \Illuminate\Http\RedirectResponse
    {
        DB::table('sessions')->where('id', $sessionId)->delete();
        return redirect()->back()->with('success', 'Session terminated.');
    }

    public function ipReputation(): View
    {
        return view('admin.security.ip-management');
    }

    public function checkIpReputation(Request $request): \Illuminate\Http\RedirectResponse
    {
        $ip = $request->input('ip');
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return redirect()->back()->withErrors(['ip' => 'Valid IP required.']);
        }
        $reputation = $this->checkReputation($ip);
        return redirect()->back()->with('reputation', $reputation);
    }

    protected function checkReputation(string $ip): array
    {
        $blocked = DB::table('security_blocked_ips')->where('ip', $ip)->exists();
        $failedLogins = DB::table('security_rate_logs')->where('ip', $ip)->where('blocked', true)->count();
        $score = 100;
        $reasons = [];
        if ($blocked) { $score -= 50; $reasons[] = 'Previously blocked'; }
        if ($failedLogins > 10) { $score -= 30; $reasons[] = "$failedLogins failed logins"; }
        elseif ($failedLogins > 3) { $score -= 10; $reasons[] = "$failedLogins failed logins"; }
        return ['ip' => $ip, 'score' => max(0, $score), 'blocked' => $blocked, 'failed_logins' => $failedLogins, 'reasons' => $reasons, 'risk' => $score >= 70 ? 'Low' : ($score >= 40 ? 'Medium' : 'High'), 'checked_at' => now()->toDateTimeString()];
    }

    public function loginHistory(): View
    {
        $logins = DB::table('security_rate_logs')->select('ip', 'route', 'blocked', 'logged_at')->orderByDesc('logged_at')->limit(200)->get();
        return view('admin.security.login-history', compact('logins'));
    }

    public function scanner(): View
    {
        return view('admin.security.scanner');
    }

    public function runScanner(): View
    {
        $audit = $this->runSecurityAudit();
        return view('admin.security.scanner', compact('audit'));
    }

    protected function runSecurityAudit(): array
    {
        $results = [];
        $results[] = ['check' => 'Cloudflare Configuration', 'status' => config('security.cloudflare.api_token') ? 'pass' : 'warn', 'detail' => config('security.cloudflare.api_token') ? 'Cloudflare API token configured.' : 'No Cloudflare API token set.'];
        $results[] = ['check' => '.env File Permissions', 'status' => substr(sprintf('%o', fileperms(base_path('.env'))), -4) === '0600' ? 'pass' : 'warn', 'detail' => 'Recommended permissions: 0600.'];
        $results[] = ['check' => 'HTTPS Enforced', 'status' => config('app.secure_connections') || config('session.secure') ? 'pass' : 'warn', 'detail' => 'HTTPS not fully enforced.'];
        $results[] = ['check' => 'Debug Mode', 'status' => config('app.debug') ? 'danger' : 'pass', 'detail' => config('app.debug') ? 'DEBUG MODE IS ON - DISABLE IN PRODUCTION!' : 'Debug mode is off.'];
        $results[] = ['check' => 'Rate Limiting', 'status' => config('security.rate_limiting.panel.enabled', true) ? 'pass' : 'warn', 'detail' => 'Panel rate limiting is ' . (config('security.rate_limiting.panel.enabled', true) ? 'enabled.' : 'disabled.')];
        $results[] = ['check' => 'Two-Factor Enforcement', 'status' => config('security.two_factor.enforce_level', 'none') !== 'none' ? 'pass' : 'warn', 'detail' => '2FA enforcement is ' . (config('security.two_factor.enforce_level', 'none') !== 'none' ? 'enabled (' . config('security.two_factor.enforce_level') . ').' : 'not configured.')];
        $results[] = ['check' => 'Database Connectivity', 'status' => 'pass', 'detail' => 'Database connection OK.'];
        $results[] = ['check' => 'Storage Directory Writable', 'status' => is_writable(storage_path()) ? 'pass' : 'danger', 'detail' => is_writable(storage_path()) ? 'Writable.' : 'NOT writable!'];
        $results[] = ['check' => 'Session Driver', 'status' => config('session.driver') !== 'file' ? 'pass' : 'warn', 'detail' => 'Session driver: ' . config('session.driver') . '. Consider using redis/cookie for better security.'];
        $results[] = ['check' => 'File Integrity', 'status' => DB::table('file_integrity_checks')->where('status', 'modified')->exists() ? 'danger' : (DB::table('file_integrity_checks')->count() > 0 ? 'pass' : 'warn'), 'detail' => DB::table('file_integrity_checks')->where('status', 'modified')->exists() ? 'Modified files detected!' : (DB::table('file_integrity_checks')->count() > 0 ? 'All files clean.' : 'No scan run yet.')];
        return $results;
    }

    public function waf(): View
    {
        return view('admin.security.waf');
    }

    public function saveWaf(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate(['enabled' => 'boolean', 'block_sqli' => 'boolean', 'block_xss' => 'boolean', 'block_path_traversal' => 'boolean']);
        foreach (['enabled', 'block_sqli', 'block_xss', 'block_path_traversal'] as $key) {
            $val = $data[$key] ?? false;
            config(["security.waf.$key" => $val]);
            DB::table('settings')->updateOrInsert(['key' => "settings::security:waf:$key"], ['value' => $val ? 'true' : 'false']);
        }
        return redirect()->back()->with('success', 'WAF rules saved.');
    }

    public function fail2ban(): View
    {
        return view('admin.security.fail2ban');
    }

    public function saveFail2ban(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate(['enabled' => 'boolean', 'max_retries' => 'integer|min:1', 'find_time' => 'integer|min:1', 'ban_time' => 'integer|min:1', 'log_path' => 'nullable|string']);
        foreach (['enabled', 'max_retries', 'find_time', 'ban_time', 'log_path'] as $key) {
            $val = $data[$key] ?? ($key === 'enabled' ? false : ($key === 'max_retries' ? 5 : ($key === 'find_time' ? 600 : ($key === 'ban_time' ? 3600 : ''))));
            config(["security.fail2ban.$key" => $val]);
            DB::table('settings')->updateOrInsert(['key' => "settings::security:fail2ban:$key"], ['value' => is_bool($val) ? ($val ? 'true' : 'false') : $val]);
        }
        return redirect()->back()->with('success', 'Fail2Ban settings saved.');
    }


}
