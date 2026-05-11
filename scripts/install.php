<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$files = [
    ROOT_DIR . '/database/schema.sql',
    ROOT_DIR . '/database/imported-wordpress.sql',
    ROOT_DIR . '/database/content-overrides.sql',
    ROOT_DIR . '/database/ifn-trust-content.sql',
    ROOT_DIR . '/database/multilingual-content-fixes.sql',
];

foreach ($files as $file) {
    if (!is_file($file)) {
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, $file . " could not be read.\n");
        exit(1);
    }

    db()->exec($sql);
    echo basename($file) . " imported.\n";
}
