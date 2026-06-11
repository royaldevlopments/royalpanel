<?php

return [
    'cloudflare' => [
        'enabled' => false,
        'api_token' => '',
        'zone_id' => '',
        'email' => '',
        'api_key' => '',
    ],

    'rate_limiting' => [
        'panel' => [
            'enabled' => true,
            'max_requests' => 60,
            'decay_minutes' => 1,
        ],
        'api' => [
            'enabled' => true,
            'max_requests' => 120,
            'decay_minutes' => 1,
        ],
        'server' => [
            'enabled' => true,
            'max_requests' => 100,
            'decay_minutes' => 1,
        ],
    ],

    'ip_blocking' => [
        'enabled' => true,
        'auto_ban' => [
            'enabled' => true,
            'max_failed_attempts' => 10,
            'ban_duration_minutes' => 60,
            'attempt_window_minutes' => 15,
        ],
        'country_block' => [
            'enabled' => false,
            'blocked_countries' => [],
            'allowed_countries' => [],
            'mode' => 'block',
        ],
    ],

    'auto_response' => [
        'enabled' => true,
        'grace_minutes' => 5,
        'settings' => [
            'under_attack_mode' => true,
            'bot_fight_mode' => true,
            'security_level' => 'high',
            'browser_integrity' => true,
            'lockdown' => true,
            'challenge_passage' => 300,
            'server_protection' => true,
        ],
    ],

    'attack_detection' => [
        'enabled' => true,
        'threshold' => [
            'requests_per_ip_per_minute' => 100,
            'concurrent_connections_per_ip' => 20,
            'unique_ips_per_minute' => 500,
        ],
        'auto_actions' => [
            'enable_under_attack_mode' => true,
            'enable_bot_fight_mode' => true,
            'block_offending_ips' => true,
            'notify_admin' => true,
        ],
        'cooldown_minutes' => 30,
    ],

    'brute_force' => [
        'enabled' => true,
        'max_attempts' => 10,
        'lockout_duration' => 60,
    ],

    'two_factor' => [
        'enforce_level' => 'none',
        'grace_period' => 7,
    ],

    'waf' => [
        'enabled' => false,
        'block_sqli' => true,
        'block_xss' => true,
        'block_path_traversal' => true,
    ],

    'fail2ban' => [
        'enabled' => false,
        'max_retries' => 5,
        'find_time' => 600,
        'ban_time' => 3600,
        'log_path' => '',
    ],

    'monitoring' => [
        'log_attacks' => true,
        'log_blocks' => true,
        'retention_days' => 30,
    ],
];
