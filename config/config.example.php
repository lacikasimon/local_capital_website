<?php

return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'database' => 'localcapital',
        'username' => 'localcapital_user',
        'password' => 'change-this-password',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => 'https://localcapital.ro',
        'session_name' => 'LC_ADMIN',
        'form_secret' => 'change-this-random-form-secret',
        'auto_apply_content_updates' => true,
        'force_content_updates' => false,
        'debug' => false,
    ],
    'security' => [
        'admin_login_max_failures' => 5,
        'admin_login_window_minutes' => 15,
        'admin_login_ban_minutes' => 30,
        'trusted_proxies' => [],
    ],
    'recaptcha' => [
        'enabled' => false,
        'site_key' => '',
        'secret_key' => '',
        'min_score' => 0.5,
        'actions' => [
            'page_view' => 0.0,
            'contact' => 0.5,
            'anaf_consent' => 0.6,
            'admin_login' => 0.7,
        ],
    ],
];
