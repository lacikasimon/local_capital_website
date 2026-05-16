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

function security_check_latest_pdf_object(string $pdf, int $objectId): string
{
    if (!preg_match_all('/(?:^|\n)' . $objectId . '\s+0\s+obj\s*(.*?)\s*endobj/s', $pdf, $matches) || empty($matches[1])) {
        return '';
    }

    return (string) end($matches[1]);
}

$checks = [];
$adminId = null;
$recordIds = [];
$testIp = '203.0.113.71';
$originalServer = $_SERVER;
$exitCode = 1;

function security_check_signature_data_url(): string
{
    $width = ANAF_SIGNATURE_CANVAS_WIDTH;
    $height = ANAF_SIGNATURE_CANVAS_HEIGHT;
    $image = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $ink = imagecolorallocate($image, 20, 28, 22);
    imagefilledrectangle($image, 0, 0, $width, $height, $white);
    imagesetthickness($image, 12);
    imageline($image, 220, 210, 430, 90, $ink);
    imageline($image, 430, 90, 650, 220, $ink);
    imageline($image, 650, 220, 1010, 95, $ink);
    imageline($image, 1010, 95, 1280, 170, $ink);
    ob_start();
    imagepng($image);
    $png = ob_get_clean();

    return 'data:image/png;base64,' . base64_encode((string) $png);
}

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
        'county' => 'Satu Mare',
        'locality' => 'Satu Mare',
        'street' => 'Vasile Lucaciu',
        'street_number' => '3',
        'building' => '',
        'stair' => '',
        'apartment' => '',
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
        'signature_image' => security_check_signature_data_url(),
        'signature_present' => '1',
    ];
    $submissionResult = save_anaf_consent_submission($draft, $submissionInput);
    security_check_mark($checks, 'Tokenized public submission succeeds', !empty($submissionResult['ok']), implode('; ', $submissionResult['errors'] ?? []));
    $downloadToken = (string) ($submissionResult['download_token'] ?? '');
    security_check_mark($checks, 'Submitter receives a PDF download token', anaf_public_pdf_download_token_is_valid($downloadToken));

    $submitted = anaf_fetch_consent($draftId);
    security_check_mark($checks, 'Submitted status is persisted', is_array($submitted) && anaf_status($submitted) === 'submitted');
    security_check_mark($checks, 'Public token is removed after submission', is_array($submitted) && (string) ($submitted['public_token'] ?? '') === '');
    security_check_mark($checks, 'Submitted token cannot be reused', anaf_fetch_consent_by_token($publicToken) === null);
    security_check_mark($checks, 'Submitter PDF token resolves submitted consent', is_array(anaf_fetch_consent_by_pdf_download_token($downloadToken)));
    $tamperedDownloadToken = $downloadToken !== '' ? substr($downloadToken, 0, -1) . (substr($downloadToken, -1) === '0' ? '1' : '0') : '';
    security_check_mark($checks, 'Tampered PDF token is rejected', anaf_fetch_consent_by_pdf_download_token($tamperedDownloadToken) === null);
    $expiredDownloadToken = is_array($submitted)
        ? anaf_public_pdf_download_token($draftId, time() - 60, (string) ($submitted['signature_hash'] ?? ''))
        : '';
    security_check_mark($checks, 'Expired PDF token is rejected', anaf_fetch_consent_by_pdf_download_token($expiredDownloadToken) === null);
    security_check_mark($checks, 'Encrypted IP can be recovered for audit PDF', is_array($submitted) && ($submitted['ip_address'] ?? '') === $testIp);

    $rawStmt = $db->prepare('SELECT cnp_enc, first_name_enc, ip_address_enc, signature_image_enc, evidence_hash, evidence_seal, public_token_hash FROM anaf_consents WHERE id = ?');
    $rawStmt->execute([$draftId]);
    $raw = $rawStmt->fetch();
    security_check_mark($checks, 'CNP is not stored in plaintext', is_array($raw) && !str_contains((string) $raw['cnp_enc'], $validData['cnp']));
    security_check_mark($checks, 'Name is not stored in plaintext', is_array($raw) && !str_contains((string) $raw['first_name_enc'], $validData['first_name']));
    security_check_mark($checks, 'IP address is not stored in plaintext', is_array($raw) && !str_contains((string) $raw['ip_address_enc'], $testIp));
    security_check_mark($checks, 'Signature image is encrypted at rest', is_array($raw) && str_starts_with((string) $raw['signature_image_enc'], 'gcm1:'));
    security_check_mark($checks, 'Evidence hash is stored for audit', is_array($raw) && preg_match('/^[a-f0-9]{64}$/', (string) $raw['evidence_hash']) === 1);
    security_check_mark($checks, 'Evidence seal is stored for tamper checks', is_array($raw) && hash_equals(anaf_evidence_seal((string) $raw['evidence_hash']), (string) $raw['evidence_seal']));
    security_check_mark($checks, 'Token hash is cleared from submitted record', is_array($raw) && $raw['public_token_hash'] === null);

    $pdf = anaf_consent_pdf_document($submitted);
    security_check_mark($checks, 'PDF generation returns a PDF document', str_starts_with($pdf, '%PDF-'));
    security_check_mark($checks, 'PDF is based on ANAF template', str_contains($pdf, '/T(Nume)') || str_contains($pdf, '/T(Prenume)'));
    security_check_mark($checks, 'PDF includes clear signer name in audit area', str_contains($pdf, 'Semnatar: Popescu Ana'));
    security_check_mark($checks, 'PDF includes electronic acceptance IP', str_contains($pdf, $testIp));
    security_check_mark($checks, 'PDF includes evidence hash marker', is_array($raw) && str_contains($pdf, 'Audit hash: ' . substr((string) $raw['evidence_hash'], 0, 18) . '...'));
    security_check_mark($checks, 'PDF includes submitted CNP value', str_contains($pdf, strtoupper(bin2hex(mb_convert_encoding($validData['cnp'], 'UTF-16BE', 'UTF-8')))));
    security_check_mark($checks, 'PDF filename includes signer name and CNP suffix', anaf_pdf_download_filename($submitted) === 'acord-anaf-popescu-ana-cnp-221144.pdf');
    $signatureField = security_check_latest_pdf_object($pdf, 23);
    $acroForm = security_check_latest_pdf_object($pdf, 188);
    $pageOne = security_check_latest_pdf_object($pdf, 166);
    $pageTwo = security_check_latest_pdf_object($pdf, 1);
    security_check_mark($checks, 'PDF signature field has no text value overlay', $signatureField !== '' && !str_contains($signatureField, '/V'), $signatureField);
    security_check_mark($checks, 'PDF signature field is read-only', $signatureField !== '' && str_contains($signatureField, '/Ff 1'), $signatureField);
    security_check_mark($checks, 'PDF keeps normalized signature canvas ratio', str_contains($pdf, '/Width ' . ANAF_SIGNATURE_CANVAS_WIDTH . ' /Height ' . ANAF_SIGNATURE_CANVAS_HEIGHT));
    security_check_mark($checks, 'PDF audit block uses stable Helvetica font', str_contains($pageTwo, '/Helv 22 0 R') && str_contains($pdf, '/Helv 5.4 Tf'));
    security_check_mark($checks, 'PDF audit hashes are shortened to fit signature area', is_array($submitted) && str_contains($pdf, 'Semnatura hash: ' . substr((string) $submitted['signature_hash'], 0, 18) . '...'));
    security_check_mark($checks, 'PDF form fields are flattened for download', $acroForm !== '' && str_contains($acroForm, '/Fields[]') && !str_contains($pageOne, '/Annots') && !str_contains($pageTwo, '/Annots'));
    security_check_mark($checks, 'PDF viewer must not regenerate form appearances', $acroForm !== '' && str_contains($acroForm, '/NeedAppearances false') && !str_contains($acroForm, '/NeedAppearances true'), $acroForm);

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
