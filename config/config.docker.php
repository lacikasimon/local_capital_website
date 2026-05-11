<?php

$recaptchaEnabled = filter_var(getenv('RECAPTCHA_ENABLED') ?: false, FILTER_VALIDATE_BOOLEAN);

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'mysql',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'database' => getenv('DB_DATABASE') ?: 'localcapital',
        'username' => getenv('DB_USERNAME') ?: 'localcapital',
        'password' => getenv('DB_PASSWORD') ?: 'localcapital',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => getenv('APP_BASE_URL') ?: 'http://localhost:8080',
        'session_name' => 'LC_ADMIN_DOCKER',
        'form_secret' => getenv('APP_FORM_SECRET') ?: 'localcapital-docker-form-secret',
        'debug' => true,
    ],
    'security' => [
        'admin_login_max_failures' => (int) (getenv('ADMIN_LOGIN_MAX_FAILURES') ?: 5),
        'admin_login_window_minutes' => (int) (getenv('ADMIN_LOGIN_WINDOW_MINUTES') ?: 15),
        'admin_login_ban_minutes' => (int) (getenv('ADMIN_LOGIN_BAN_MINUTES') ?: 30),
    ],
    'recaptcha' => [
        'enabled' => $recaptchaEnabled,
        'site_key' => getenv('RECAPTCHA_SITE_KEY') ?: '',
        'secret_key' => getenv('RECAPTCHA_SECRET_KEY') ?: '',
        'min_score' => (float) (getenv('RECAPTCHA_MIN_SCORE') ?: 0.5),
        'actions' => [
            'page_view' => 0.0,
            'contact' => (float) (getenv('RECAPTCHA_CONTACT_SCORE') ?: 0.5),
            'anaf_consent' => (float) (getenv('RECAPTCHA_ANAF_CONSENT_SCORE') ?: 0.6),
            'admin_login' => (float) (getenv('RECAPTCHA_ADMIN_SCORE') ?: 0.7),
        ],
    ],
];
