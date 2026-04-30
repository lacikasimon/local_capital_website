<?php

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
];
