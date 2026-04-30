<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

$tables = [
    'settings' => ['setting_value'],
    'navigation' => ['label'],
    'pages' => ['title', 'summary', 'body', 'cta_label', 'secondary_cta_label', 'extra_json'],
    'posts' => ['title', 'excerpt', 'body'],
    'site_links' => ['label'],
];

$updated = 0;

foreach ($tables as $table => $columns) {
    $stmt = db()->query('SELECT id, ' . implode(', ', $columns) . ' FROM ' . $table);
    foreach ($stmt->fetchAll() as $row) {
        $changes = [];
        foreach ($columns as $column) {
            if ($row[$column] === null) {
                continue;
            }
            $repaired = repair_text_encoding((string) $row[$column]);
            if ($repaired !== $row[$column]) {
                $changes[$column] = $repaired;
            }
        }

        if (!$changes) {
            continue;
        }

        $assignments = implode(', ', array_map(fn ($column) => $column . ' = ?', array_keys($changes)));
        $update = db()->prepare('UPDATE ' . $table . ' SET ' . $assignments . ' WHERE id = ?');
        $update->execute([...array_values($changes), $row['id']]);
        $updated += count($changes);
    }
}

echo 'Repaired text fields: ' . $updated . PHP_EOL;
