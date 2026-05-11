<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/views.php';
require __DIR__ . '/../app/admin.php';
require __DIR__ . '/../app/anaf.php';

function security_check_mark(array &$checks, string $name, bool $ok, string $detail = ''): void
{
    $checks[] = [
        'name' => $name,
        'status' => $ok ? 'ok' : 'fail',
        'detail' => $detail,
    ];
}

$checks = [];
$adminId = null;
$recordIds = [];
$testIp = '203.0.113.71';
$originalServer = $_SERVER;
$exitCode = 1;

try {
    $_SERVER['REMOTE_ADDR'] = $testIp;
    $_SERVER['HTTP_USER_AGENT'] = 'LocalCapitalSecurityCheck/1.0';
    $_SERVER['HTTP_HOST'] = 'localhost:8080';
    $_SERVER['HTTPS'] = 'off';

    ensure_anaf_consent_tables();
    $db = db();
    $site = load_site('ro');

    $username = 'security_check_' . bin2hex(random_bytes(4));
    $db->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)')
        ->execute([$username, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)]);
    $adminId = (int) $db->lastInsertId();
    $admin = ['id' => $adminId, 'username' => $username];

    $validData = [
        'request_reference' => 'LC-SEC-TEST',
        'last_name' => 'Popescu',
        'first_name' => 'Ana',
        'cnp' => '1800101221144',
        'id_series' => 'SM',
        'id_number' => '123456',
        'id_issued_by' => 'SPCLEP Satu Mare',
        'id_issued_at' => '2020-01-01',
        'email' => 'security-check@example.test',
        'phone' => '+40722123456',
        'address' => 'Str. Testului nr. 1, Satu Mare',
    ];

    $badDate = $validData;
    $badDate['id_issued_at'] = (new DateTimeImmutable('tomorrow'))->format('Y-m-d');
    [, $badDateErrors] = anaf_normalize_input($badDate, true);
    security_check_mark($checks, 'Rejects future CI issue date', $badDateErrors !== [], implode('; ', $badDateErrors));

    $_POST = $validData;
    $draftId = create_anaf_consent_draft($site, $admin);
    $recordIds[] = $draftId;
    $draft = anaf_fetch_consent($draftId);
    security_check_mark($checks, 'Draft can be created by admin flow', is_array($draft) && anaf_status($draft) === 'draft');

    $publicToken = (string) ($draft['public_token'] ?? '');
    security_check_mark($checks, 'Draft has a one-time public token', strlen($publicToken) >= 32);

    $submissionInput = $validData + [
        'anaf_form_token' => contact_form_token(),
        'public_token' => $publicToken,
        'company_website' => '',
        'recaptcha_token' => '',
        'anaf_consent' => '1',
    ];
    $submissionResult = save_anaf_consent_submission($draft, $submissionInput);
    security_check_mark($checks, 'Tokenized public submission succeeds', !empty($submissionResult['ok']), implode('; ', $submissionResult['errors'] ?? []));

    $submitted = anaf_fetch_consent($draftId);
    security_check_mark($checks, 'Submitted status is persisted', is_array($submitted) && anaf_status($submitted) === 'submitted');
    security_check_mark($checks, 'Public token is removed after submission', is_array($submitted) && (string) ($submitted['public_token'] ?? '') === '');
    security_check_mark($checks, 'Submitted token cannot be reused', anaf_fetch_consent_by_token($publicToken) === null);
    security_check_mark($checks, 'Encrypted IP can be recovered for audit PDF', is_array($submitted) && ($submitted['ip_address'] ?? '') === $testIp);

    $rawStmt = $db->prepare('SELECT cnp_enc, first_name_enc, ip_address_enc, public_token_hash FROM anaf_consents WHERE id = ?');
    $rawStmt->execute([$draftId]);
    $raw = $rawStmt->fetch();
    security_check_mark($checks, 'CNP is not stored in plaintext', is_array($raw) && !str_contains((string) $raw['cnp_enc'], $validData['cnp']));
    security_check_mark($checks, 'Name is not stored in plaintext', is_array($raw) && !str_contains((string) $raw['first_name_enc'], $validData['first_name']));
    security_check_mark($checks, 'IP address is not stored in plaintext', is_array($raw) && !str_contains((string) $raw['ip_address_enc'], $testIp));
    security_check_mark($checks, 'Token hash is cleared from submitted record', is_array($raw) && $raw['public_token_hash'] === null);

    $pdf = anaf_consent_pdf_document($submitted);
    security_check_mark($checks, 'PDF generation returns a PDF document', str_starts_with($pdf, '%PDF-1.4'));
    security_check_mark($checks, 'PDF includes EXEMPLU marker', str_contains($pdf, 'EXEMPLU'));
    security_check_mark($checks, 'PDF includes electronic acceptance IP', str_contains($pdf, $testIp));

    $ok = !array_filter($checks, static fn (array $check): bool => $check['status'] !== 'ok');
    echo json_encode(['ok' => $ok, 'checks' => $checks], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    $exitCode = $ok ? 0 : 1;
} finally {
    $_SERVER = $originalServer;

    try {
        $db = db();
        $ipHash = hash_hmac('sha256', $testIp, form_secret());
        $db->prepare('DELETE FROM anaf_consent_attempts WHERE ip_hash = ?')->execute([$ipHash]);
        if ($recordIds) {
            $placeholders = implode(',', array_fill(0, count($recordIds), '?'));
            $db->prepare('DELETE FROM anaf_consents WHERE id IN (' . $placeholders . ')')->execute($recordIds);
        }
        if ($adminId !== null) {
            $db->prepare('DELETE FROM admin_users WHERE id = ?')->execute([$adminId]);
        }
    } catch (Throwable) {
        // Cleanup must not hide the actual test result.
    }
}

exit($exitCode);
