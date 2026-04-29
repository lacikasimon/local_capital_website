<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$username = $argv[1] ?? '';
$password = getenv('ADMIN_PASSWORD') ?: '';

if ($username === '' || $password === '') {
    fwrite(STDERR, "Usage: ADMIN_PASSWORD='strong-password' php scripts/create-admin.php admin\n");
    exit(1);
}

if (strlen($password) < 12) {
    fwrite(STDERR, "Choose a password with at least 12 characters.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = db()->prepare('
    INSERT INTO admin_users (username, password_hash)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), updated_at = CURRENT_TIMESTAMP
');
$stmt->execute([$username, $hash]);

echo 'Admin user "' . $username . "\" saved.\n";
