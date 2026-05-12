<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$dryRun = in_array('--dry-run', $argv, true);
$results = $dryRun
    ? apply_content_updates(true, content_update_force_enabled(), 30, 2)
    : apply_content_updates_with_lock(content_update_force_enabled(), 30, 2);
foreach ($results as $result) {
    $file = (string) $result['file'];
    $status = (string) $result['status'];
    $checksum = isset($result['checksum']) ? ' (' . $result['checksum'] . ')' : '';
    echo $file . ' ' . $status . $checksum . ".\n";
}
