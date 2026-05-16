<?php

$recaptchaEnabled = filter_var(getenv('RECAPTCHA_ENABLED') ?: false, FILTER_VALIDATE_BOOLEAN);
$autoApplyContent = getenv('LOCALCAPITAL_AUTO_APPLY_CONTENT');
$forceContentUpdates = getenv('LOCALCAPITAL_FORCE_CONTENT_UPDATES');
$trustedProxies = trim((string) (getenv('TRUSTED_PROXY_CIDRS') ?: ''));

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
        'auto_apply_content_updates' => $autoApplyContent === false || $autoApplyContent === ''
            ? true
            : filter_var($autoApplyContent, FILTER_VALIDATE_BOOLEAN),
        'force_content_updates' => $forceContentUpdates === false || $forceContentUpdates === ''
            ? false
            : filter_var($forceContentUpdates, FILTER_VALIDATE_BOOLEAN),
        'debug' => true,
    ],
    'security' => [
        'admin_login_max_failures' => (int) (getenv('ADMIN_LOGIN_MAX_FAILURES') ?: 5),
        'admin_login_window_minutes' => (int) (getenv('ADMIN_LOGIN_WINDOW_MINUTES') ?: 15),
        'admin_login_ban_minutes' => (int) (getenv('ADMIN_LOGIN_BAN_MINUTES') ?: 30),
        'trusted_proxies' => $trustedProxies !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $trustedProxies))))
            : [],
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
