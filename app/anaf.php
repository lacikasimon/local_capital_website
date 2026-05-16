<?php

declare(strict_types=1);

const ANAF_CONSENT_VERSION = 'anaf-consent-v3-template-2026-05-15';
const ANAF_PUBLIC_PATH = '/acord-anaf';
const ANAF_TEMPLATE_PDF = ROOT_DIR . '/resources/acord-anaf-template.pdf';
const ANAF_PUBLIC_PDF_TOKEN_TTL = 604800;
const ANAF_SIGNATURE_CANVAS_WIDTH = 1500;
const ANAF_SIGNATURE_CANVAS_HEIGHT = 320;

function anaf_consent_text(): string
{
    return trim(<<<'TEXT'
ACORD din data:
de consultare, transmitere și prelucrare a informațiilor din bazele de date ale Ministerului Finanțelor Publice, Agenția Națională de Administrare Fiscală, precum și a datelor cu caracter personal aparținând clientului.

Subsemnatul/Subsemnata, în calitate de împrumutat, declar în mod expres următoarele:

(A) Declarații referitoare la consultarea informațiilor din bazele de date ale Agenției Naționale de Administrare Fiscală: am fost informat că societatea LOCAL CAPITAL IFN S.A., cu sediul social în municipiul Satu Mare, strada Anghel Saligny, nr. 21, județ Satu Mare, cu sediu ales/punct de lucru în municipiul Satu Mare, Bulevardul Vasile Lucaciu, nr. 3, județ Satu Mare, cod poștal 447230, având Cod Unic de Înregistrare 46376462, număr de înregistrare în Registrul Comerțului J30/702/2022, înregistrată în Registrul General al B.N.R. sub numărul RG-PJR-31-110391/21.03.2023, denumită în cele ce urmează destinatarul, dorește să consulte pe o durată maximă de 5 zile lucrătoare de la data semnării prezentului acord și să prelucreze informațiile, inclusiv datele mele cu caracter personal indicate mai jos, înregistrate pe numele subsemnatului/subsemnatei/subscrisei în evidențele Ministerului Finanțelor Publice, Agenția Națională de Administrare Fiscală (ANAF), în scopul inițierii sau derulării relațiilor contractuale specifice activității de creditare a destinatarului, realizării intereselor legitime ale destinatarului și/sau îndeplinirii obligațiilor legale ce îi revin destinatarului.

Datele pot include:
a) date de identificare ale persoanei fizice/persoanei juridice ce exercită o activitate autorizată: numele și prenumele, adresa de domiciliu/reședință, codul numeric personal/codul unic de identificare/numărul de identificare fiscală (NIF), seria și numărul actului de identitate în cazul persoanelor nerezidente;
b) denumirea formei de exercitare a profesiei/de realizare a veniturilor, codul de identificare fiscală a formei de exercitare a profesiei/de realizare a veniturilor, adresa/sediul formei de exercitare a profesiei/de realizare a veniturilor;
c) date de identificare ale persoanei fizice, reprezentant legal al persoanei juridice: numele și prenumele, codul numeric personal/codul unic de identificare;
d) veniturile realizate din orice fel de activități (salariale, autorizate/independente, pensii, asigurări sociale, închirieri etc.).

(B) Declarații referitoare la transmiterea către și prelucrarea de către Agenția Națională de Administrare Fiscală a datelor cu caracter personal ale subsemnatului(ei), respectiv ale reprezentanților subscrisei, deținute de către instituția financiară nebancară. Îmi exprim în mod expres consimțământul ca, în măsura în care mă voi regăsi în una sau mai multe dintre situațiile de mai jos, destinatarul să transmită lunar către ANAF:
a) informațiile, inclusiv datele mele cu caracter personal indicate mai jos, înregistrate pe numele subsemnatului/subsemnatei/subscrisei în evidențele destinatarului, în scopul de a răspunde tuturor solicitărilor ANAF referitoare la activitatea de creditare (aprobate sau respinse) din luna anterioară, respectiv identitatea contribuabilului/clientului, tipul contractului de credit, cerere de creditare aprobată sau refuzată și informații privind creditul și tranzacțiile;
b) date referitoare la scrisorile de garanție, respectiv suma garantată, identitatea beneficiarului, identitatea solicitantului, termen de valabilitate - data expirării;
c) copia dosarelor de creditare, cu toată documentația aferentă depusă;
d) în măsura în care mă voi regăsi în una sau mai multe dintre situațiile de mai sus, destinatarul să transmită oricând către ANAF informațiile prevăzute la lit. B lit. a), b) și c) corespunzătoare perioadei începând cu 1 ianuarie 2023 și până la data semnării prezentului acord.

Înțeleg și accept că dezacordul meu (răspunsul NU) conduce la imposibilitatea instituției financiare nebancare de a interoga electronic baza de date a ANAF cu privire la informațiile de la lit. A. În consecință, înțeleg și accept să fac dovada veniturilor în modalitățile prevăzute de lege, așa cum îmi sunt solicitate de către instituția financiară nebancară.

(C) Declarații referitoare la drepturile persoanei vizate/care a dat declarațiile de mai sus. Am luat cunoștință de faptul că îmi pot exercita toate drepturile prevăzute de Regulamentul (UE) nr. 2016/679 privind protecția persoanelor fizice în ceea ce privește prelucrarea datelor cu caracter personal, inclusiv dreptul la informare și acces, rectificare, ștergere, restricționare, portabilitate, opoziție și dreptul de a mă adresa Autorității Naționale de Supraveghere a Prelucrării Datelor cu Caracter Personal sau justiției.

Confirm că datele completate în formular sunt corecte, că acordul este exprimat în mod liber, specific, informat și neechivoc, iar semnătura desenată electronic îmi aparține.
TEXT);
}

function ensure_anaf_consent_tables(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }

    db()->exec('CREATE TABLE IF NOT EXISTS anaf_consents (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        status VARCHAR(20) NOT NULL DEFAULT \'draft\',
        public_token_hash CHAR(64) NULL,
        public_token_enc TEXT NULL,
        token_expires_at DATETIME NULL,
        request_reference_enc TEXT NOT NULL,
        first_name_enc TEXT NOT NULL,
        last_name_enc TEXT NOT NULL,
        cnp_enc TEXT NOT NULL,
        county_enc TEXT NULL,
        locality_enc TEXT NULL,
        street_enc TEXT NULL,
        street_number_enc TEXT NULL,
        building_enc TEXT NULL,
        stair_enc TEXT NULL,
        apartment_enc TEXT NULL,
        id_series_enc TEXT NOT NULL,
        id_number_enc TEXT NOT NULL,
        id_issued_by_enc TEXT NOT NULL,
        id_issued_at_enc TEXT NOT NULL,
        email_enc TEXT NOT NULL,
        phone_enc TEXT NOT NULL,
        address_enc TEXT NOT NULL,
        consent_anaf TINYINT(1) NOT NULL DEFAULT 0,
        consent_text_version VARCHAR(60) NOT NULL DEFAULT \'\',
        consent_text TEXT NULL,
        signature_image_enc MEDIUMTEXT NULL,
        signature_hash CHAR(64) NULL,
        evidence_hash CHAR(64) NULL,
        evidence_seal CHAR(64) NULL,
        ip_address_enc TEXT NULL,
        ip_hash CHAR(64) NULL,
        user_agent_hash CHAR(64) NULL,
        created_by_admin_id INT UNSIGNED NULL,
        submitted_at DATETIME NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_anaf_public_token_hash (public_token_hash),
        KEY idx_anaf_status_created (status, created_at),
        KEY idx_anaf_submitted_at (submitted_at),
        KEY idx_anaf_token_expires (token_expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    db()->exec('CREATE TABLE IF NOT EXISTS anaf_consent_attempts (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        ip_hash CHAR(64) NOT NULL,
        public_token_hash CHAR(64) NULL,
        success TINYINT(1) NOT NULL DEFAULT 0,
        attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_anaf_attempt_ip_time (ip_hash, attempted_at),
        KEY idx_anaf_attempt_token_time (public_token_hash, attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    anaf_ensure_consent_columns();

    $ready = true;
}

function anaf_ensure_consent_columns(): void
{
    $columns = [
        'request_reference_enc' => ['definition' => 'TEXT NULL', 'after' => 'token_expires_at'],
        'county_enc' => ['definition' => 'TEXT NULL', 'after' => 'cnp_enc'],
        'locality_enc' => ['definition' => 'TEXT NULL', 'after' => 'county_enc'],
        'street_enc' => ['definition' => 'TEXT NULL', 'after' => 'locality_enc'],
        'street_number_enc' => ['definition' => 'TEXT NULL', 'after' => 'street_enc'],
        'building_enc' => ['definition' => 'TEXT NULL', 'after' => 'street_number_enc'],
        'stair_enc' => ['definition' => 'TEXT NULL', 'after' => 'building_enc'],
        'apartment_enc' => ['definition' => 'TEXT NULL', 'after' => 'stair_enc'],
        'id_issued_by_enc' => ['definition' => 'TEXT NULL', 'after' => 'id_number_enc'],
        'id_issued_at_enc' => ['definition' => 'TEXT NULL', 'after' => 'id_issued_by_enc'],
        'signature_image_enc' => ['definition' => 'MEDIUMTEXT NULL', 'after' => 'consent_text'],
        'signature_hash' => ['definition' => 'CHAR(64) NULL', 'after' => 'signature_image_enc'],
        'evidence_hash' => ['definition' => 'CHAR(64) NULL', 'after' => 'signature_hash'],
        'evidence_seal' => ['definition' => 'CHAR(64) NULL', 'after' => 'evidence_hash'],
        'ip_address_enc' => ['definition' => 'TEXT NULL', 'after' => 'consent_text'],
    ];

    foreach ($columns as $column => $meta) {
        $stmt = db()->query('SHOW COLUMNS FROM anaf_consents LIKE ' . db()->quote($column));
        if ($stmt->fetch()) {
            continue;
        }

        $safeColumn = '`' . str_replace('`', '``', $column) . '`';
        $safeAfter = '`' . str_replace('`', '``', (string) $meta['after']) . '`';
        db()->exec('ALTER TABLE anaf_consents ADD COLUMN ' . $safeColumn . ' ' . $meta['definition'] . ' AFTER ' . $safeAfter);
    }
}

function anaf_sensitive_key(): string
{
    return hash('sha256', 'anaf-consent|' . form_secret(), true);
}

function anaf_encrypt_value(?string $value): string
{
    $value = (string) ($value ?? '');
    if ($value === '') {
        return '';
    }
    if (!function_exists('openssl_encrypt')) {
        throw new RuntimeException('OpenSSL is required for ANAF consent encryption.');
    }

    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt($value, 'aes-256-gcm', anaf_sensitive_key(), OPENSSL_RAW_DATA, $iv, $tag, 'localcapital-anaf');
    if ($ciphertext === false || $tag === '') {
        throw new RuntimeException('ANAF consent encryption failed.');
    }

    return 'gcm1:' . base64_encode($iv . $tag . $ciphertext);
}

function anaf_decrypt_value(?string $payload): string
{
    $payload = (string) ($payload ?? '');
    if ($payload === '') {
        return '';
    }
    if (!str_starts_with($payload, 'gcm1:') || !function_exists('openssl_decrypt')) {
        return '';
    }

    $raw = base64_decode(substr($payload, 5), true);
    if (!is_string($raw) || strlen($raw) < 29) {
        return '';
    }

    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $ciphertext = substr($raw, 28);
    $plain = openssl_decrypt($ciphertext, 'aes-256-gcm', anaf_sensitive_key(), OPENSSL_RAW_DATA, $iv, $tag, 'localcapital-anaf');

    return is_string($plain) ? $plain : '';
}

function anaf_public_token(): string
{
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
}

function anaf_token_hash(string $token): string
{
    return hash_hmac('sha256', $token, form_secret());
}

function anaf_public_pdf_download_token(int $id, int $expiresAt, string $signatureHash): string
{
    $payload = $id . '-' . $expiresAt;
    $signature = hash_hmac('sha256', 'anaf-pdf|' . $payload . '|' . $signatureHash, form_secret());

    return $payload . '-' . $signature;
}

function anaf_public_pdf_download_url(string $token): string
{
    return ANAF_PUBLIC_PATH . '/pdf/' . rawurlencode($token);
}

function anaf_public_pdf_download_token_is_valid(string $token): bool
{
    return preg_match('/^[1-9][0-9]*-[1-9][0-9]*-[a-f0-9]{64}$/', $token) === 1;
}

function anaf_evidence_payload(array $data, string $signatureHash, string $ipHash, string $userAgentHash, string $submittedAt): array
{
    return [
        'consent_text_hash' => hash('sha256', anaf_consent_text()),
        'consent_version' => ANAF_CONSENT_VERSION,
        'fields' => array_intersect_key($data, anaf_empty_data()),
        'ip_hash' => $ipHash,
        'signature_hash' => $signatureHash,
        'submitted_at' => $submittedAt,
        'user_agent_hash' => $userAgentHash,
    ];
}

function anaf_evidence_hash(array $data, string $signatureHash, string $ipHash, string $userAgentHash, string $submittedAt): string
{
    $payload = json_encode(
        anaf_evidence_payload($data, $signatureHash, $ipHash, $userAgentHash, $submittedAt),
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );

    return hash('sha256', is_string($payload) ? $payload : '');
}

function anaf_evidence_seal(string $evidenceHash): string
{
    return hash_hmac('sha256', 'anaf-evidence|' . $evidenceHash, form_secret());
}

function anaf_user_agent_hash(): string
{
    return hash_hmac('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), form_secret());
}

function anaf_public_route_token(string $path): ?string
{
    $path = normalize_route_path($path);
    if ($path === ANAF_PUBLIC_PATH) {
        return '';
    }
    if (preg_match('#^' . preg_quote(ANAF_PUBLIC_PATH, '#') . '/([A-Za-z0-9_-]{24,120})$#', $path, $match)) {
        return $match[1];
    }

    return null;
}

function anaf_fields(): array
{
    return [
        'request_reference' => ['label' => 'Număr cerere/dosar Local Capital (opțional)', 'max' => 80, 'required' => false],
        'last_name' => ['label' => 'Nume', 'max' => 120, 'required' => true],
        'first_name' => ['label' => 'Prenume', 'max' => 120, 'required' => true],
        'cnp' => ['label' => 'CNP', 'max' => 13, 'required' => true],
        'county' => ['label' => 'Județ CI', 'max' => 80, 'required' => true],
        'locality' => ['label' => 'Localitate CI', 'max' => 120, 'required' => true],
        'street' => ['label' => 'Stradă CI', 'max' => 160, 'required' => true],
        'street_number' => ['label' => 'Nr.', 'max' => 20, 'required' => true],
        'building' => ['label' => 'Bl.', 'max' => 20, 'required' => false],
        'stair' => ['label' => 'Sc.', 'max' => 20, 'required' => false],
        'apartment' => ['label' => 'Ap.', 'max' => 20, 'required' => false],
        'id_series' => ['label' => 'Serie CI', 'max' => 2, 'required' => true],
        'id_number' => ['label' => 'Număr CI', 'max' => 6, 'required' => true],
        'id_issued_by' => ['label' => 'CI emis de', 'max' => 120, 'required' => true],
        'id_issued_at' => ['label' => 'Data emiterii CI', 'max' => 10, 'required' => true],
        'email' => ['label' => 'Email', 'max' => 190, 'required' => true],
        'phone' => ['label' => 'Telefon', 'max' => 40, 'required' => true],
        'address' => ['label' => 'Adresă completă (opțional)', 'max' => 600, 'required' => false],
    ];
}

function anaf_empty_data(): array
{
    return array_fill_keys(array_keys(anaf_fields()), '');
}

function anaf_compose_address(array $data): string
{
    $parts = [];
    foreach ([
        'Județ' => 'county',
        'Localitate' => 'locality',
        'Str.' => 'street',
        'nr.' => 'street_number',
        'bl.' => 'building',
        'sc.' => 'stair',
        'ap.' => 'apartment',
    ] as $label => $field) {
        $value = trim((string) ($data[$field] ?? ''));
        if ($value !== '') {
            $parts[] = $label . ' ' . $value;
        }
    }

    return implode(', ', $parts);
}

function anaf_decrypted_row(array $row): array
{
    foreach (array_keys(anaf_fields()) as $field) {
        $row[$field] = repair_text_encoding(anaf_decrypt_value($row[$field . '_enc'] ?? ''));
    }
    $row['public_token'] = anaf_decrypt_value($row['public_token_enc'] ?? '');
    $row['ip_address'] = anaf_decrypt_value($row['ip_address_enc'] ?? '');
    $row['signature_image'] = anaf_decrypt_value($row['signature_image_enc'] ?? '');
    if (($row['address'] ?? '') === '') {
        $row['address'] = anaf_compose_address($row);
    }

    return $row;
}

function anaf_fetch_consent(int $id): ?array
{
    ensure_anaf_consent_tables();

    $stmt = db()->prepare('SELECT * FROM anaf_consents WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    return $row ? anaf_decrypted_row($row) : null;
}

function anaf_fetch_consent_by_token(string $token): ?array
{
    ensure_anaf_consent_tables();
    if ($token === '') {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM anaf_consents WHERE public_token_hash = ? LIMIT 1');
    $stmt->execute([anaf_token_hash($token)]);
    $row = $stmt->fetch();

    return $row ? anaf_decrypted_row($row) : null;
}

function anaf_fetch_consent_by_pdf_download_token(string $token): ?array
{
    if (!anaf_public_pdf_download_token_is_valid($token)) {
        return null;
    }

    [$id, $expiresAt, $signature] = explode('-', $token, 3);
    if ((int) $expiresAt < time()) {
        return null;
    }

    $row = anaf_fetch_consent((int) $id);
    if (!$row || anaf_status($row) !== 'submitted') {
        return null;
    }

    $signatureHash = (string) ($row['signature_hash'] ?? '');
    if ($signatureHash === '') {
        return null;
    }

    $expected = anaf_public_pdf_download_token((int) $id, (int) $expiresAt, $signatureHash);

    return hash_equals($expected, $token) ? $row : null;
}

function anaf_status(array $row): string
{
    $status = (string) ($row['status'] ?? 'draft');
    $expiresAt = (string) ($row['token_expires_at'] ?? '');
    if ($status === 'draft' && $expiresAt !== '' && strtotime($expiresAt) !== false && strtotime($expiresAt) < time()) {
        return 'expired';
    }

    return $status;
}

function anaf_status_label(string $status): string
{
    return [
        'draft' => 'link activ',
        'expired' => 'expirat',
        'submitted' => 'acceptat',
        'archived' => 'arhivat',
    ][$status] ?? label_from_key($status);
}

function anaf_mask_cnp(string $cnp): string
{
    $cnp = preg_replace('/\D+/', '', $cnp) ?? '';
    if (strlen($cnp) < 4) {
        return '';
    }

    return str_repeat('*', max(0, strlen($cnp) - 4)) . substr($cnp, -4);
}

function anaf_public_url(array $row): string
{
    $token = (string) ($row['public_token'] ?? '');
    if ($token === '') {
        return '';
    }

    return absolute_url(ANAF_PUBLIC_PATH . '/' . $token);
}

function anaf_clean_field(string $value, int $limit): string
{
    return clean_form_text($value, $limit);
}

function anaf_normalize_input(array $input, bool $requireAll): array
{
    $data = anaf_empty_data();
    $errors = [];

    foreach (anaf_fields() as $field => $meta) {
        $value = anaf_clean_field((string) ($input[$field] ?? ''), (int) $meta['max']);
        if ($field === 'cnp') {
            $value = preg_replace('/\D+/', '', $value) ?? '';
        } elseif ($field === 'id_series') {
            $value = strtoupper(preg_replace('/[^a-z]/i', '', $value) ?? '');
        } elseif ($field === 'id_number') {
            $value = preg_replace('/\D+/', '', $value) ?? '';
        } elseif (in_array($field, ['street_number', 'building', 'stair', 'apartment'], true)) {
            $value = strtoupper(preg_replace('/[^0-9A-Z .\/-]/i', '', $value) ?? '');
        }
        $data[$field] = $value;

        if ($requireAll && !empty($meta['required']) && $value === '') {
            $errors[] = 'Completează câmpul: ' . $meta['label'] . '.';
        }
    }

    if ($data['cnp'] !== '' && !anaf_cnp_is_valid($data['cnp'])) {
        $errors[] = 'CNP-ul nu este valid.';
    }
    if ($data['id_series'] !== '' && !preg_match('/^[A-Z]{2}$/', $data['id_series'])) {
        $errors[] = 'Seria CI trebuie să conțină două litere.';
    }
    if ($data['id_number'] !== '' && !preg_match('/^[0-9]{6}$/', $data['id_number'])) {
        $errors[] = 'Numărul CI trebuie să conțină 6 cifre.';
    }
    if ($data['id_issued_at'] !== '') {
        $issuedAt = DateTimeImmutable::createFromFormat('!Y-m-d', $data['id_issued_at']);
        $issuedErrors = DateTimeImmutable::getLastErrors();
        if (
            !$issuedAt
            || ($issuedErrors !== false && ($issuedErrors['warning_count'] > 0 || $issuedErrors['error_count'] > 0))
            || $issuedAt > new DateTimeImmutable('today')
        ) {
            $errors[] = 'Data emiterii CI nu este validă.';
        }
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Adresa de email nu este validă.';
    }
    if ($data['phone'] !== '' && !preg_match('/^\+?[0-9\s().-]{7,40}$/', $data['phone'])) {
        $errors[] = 'Numărul de telefon nu este valid.';
    }
    if ($data['address'] === '') {
        $data['address'] = anaf_compose_address($data);
    }

    return [$data, $errors];
}

function anaf_cnp_is_valid(string $cnp): bool
{
    if (!preg_match('/^[1-9][0-9]{12}$/', $cnp)) {
        return false;
    }

    $weights = '279146358279';
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += (int) $cnp[$i] * (int) $weights[$i];
    }
    $control = $sum % 11;
    if ($control === 10) {
        $control = 1;
    }

    return $control === (int) $cnp[12];
}

function anaf_rate_limited(?string $tokenHash): bool
{
    ensure_anaf_consent_tables();

    db()->exec('DELETE FROM anaf_consent_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');

    $stmt = db()->prepare('SELECT COUNT(*) FROM anaf_consent_attempts WHERE ip_hash = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)');
    $stmt->execute([client_ip_hash()]);
    if ((int) $stmt->fetchColumn() >= 8) {
        return true;
    }

    if ($tokenHash !== null) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM anaf_consent_attempts WHERE public_token_hash = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)');
        $stmt->execute([$tokenHash]);
        return (int) $stmt->fetchColumn() >= 8;
    }

    return false;
}

function anaf_record_attempt(?string $tokenHash, bool $success): void
{
    ensure_anaf_consent_tables();

    $stmt = db()->prepare('INSERT INTO anaf_consent_attempts (ip_hash, public_token_hash, success) VALUES (?, ?, ?)');
    $stmt->execute([client_ip_hash(), $tokenHash, $success ? 1 : 0]);
}

function anaf_form_action(?string $token): string
{
    return ANAF_PUBLIC_PATH . ($token ? '/' . rawurlencode($token) : '');
}

function anaf_validate_signature_input(array $input): array
{
    $dataUrl = trim((string) ($input['signature_image'] ?? ''));
    if ($dataUrl === '' || (string) ($input['signature_present'] ?? '') !== '1') {
        return ['', ['Semnează acordul în câmpul de semnătură.']];
    }
    if (strlen($dataUrl) > 350000) {
        return ['', ['Semnătura este prea mare. Șterge și semnează din nou.']];
    }
    if (!preg_match('#^data:image/png;base64,([A-Za-z0-9+/=\s]+)$#', $dataUrl, $match)) {
        return ['', ['Semnătura nu a putut fi citită. Șterge și semnează din nou.']];
    }

    $raw = base64_decode(preg_replace('/\s+/', '', $match[1]) ?? '', true);
    if (!is_string($raw) || strlen($raw) < 300) {
        return ['', ['Semnătura nu a putut fi citită. Șterge și semnează din nou.']];
    }

    $info = @getimagesizefromstring($raw);
    if (!is_array($info) || ($info[2] ?? null) !== IMAGETYPE_PNG || ($info[0] ?? 0) < 80 || ($info[1] ?? 0) < 40) {
        return ['', ['Semnătura nu are un format valid. Șterge și semnează din nou.']];
    }
    $source = function_exists('imagecreatefromstring') ? @imagecreatefromstring($raw) : null;
    if (!$source) {
        return ['', ['Semnătura nu conține linii vizibile. Șterge și semnează din nou.']];
    }
    $bounds = anaf_signature_ink_bounds($source, (int) $info[0], (int) $info[1]);
    if (!$bounds) {
        return ['', ['Semnătura nu conține linii vizibile. Șterge și semnează din nou.']];
    }

    return ['data:image/png;base64,' . base64_encode($raw), []];
}

function render_anaf_signature_script(): string
{
    return '<script nonce="' . e(csp_nonce()) . '">
(() => {
  const pad = document.querySelector("[data-signature-pad]");
  if (!pad) return;

  const canvas = pad.querySelector("canvas");
  const input = pad.querySelector("[data-signature-input]");
  const present = pad.querySelector("[data-signature-present]");
  const clearButton = pad.querySelector("[data-signature-clear]");
  const error = pad.querySelector("[data-signature-error]");
  const form = canvas.closest("form");
  const fields = form ? Array.from(form.querySelectorAll("[data-anaf-validate]")) : [];
  const consent = form ? form.querySelector("[name=anaf_consent]") : null;
  const consentError = form ? form.querySelector("[data-consent-error]") : null;
  const ctx = canvas.getContext("2d");
  const logicalWidth = ' . ANAF_SIGNATURE_CANVAS_WIDTH . ';
  const logicalHeight = ' . ANAF_SIGNATURE_CANVAS_HEIGHT . ';
  const touched = new WeakSet();
  let drawing = false;
  let signed = false;
  let last = null;
  let consentTouched = false;

  const cnpIsValid = (cnp) => {
    if (!/^[1-9][0-9]{12}$/.test(cnp)) return false;
    const weights = "279146358279";
    let sum = 0;
    for (let i = 0; i < 12; i++) {
      sum += Number(cnp[i]) * Number(weights[i]);
    }
    let control = sum % 11;
    if (control === 10) control = 1;
    return control === Number(cnp[12]);
  };

  const normalizeField = (field, value) => {
    if (field === "cnp" || field === "id_number") {
      return value.replace(/\D+/g, "");
    }
    if (field === "id_series") {
      return value.replace(/[^a-z]/gi, "").toUpperCase();
    }
    if (["street_number", "building", "stair", "apartment"].includes(field)) {
      return value.replace(/[^0-9A-Z .\/-]/gi, "").toUpperCase();
    }
    return value;
  };

  const fieldMessage = (input) => {
    const field = input.dataset.anafValidate || "";
    const label = input.dataset.anafLabel || input.name || "câmpul";
    const value = input.value.trim();
    const required = input.hasAttribute("required");

    if (required && value === "") return `Completează câmpul: ${label}.`;
    if (value === "") return "";

    if (field === "cnp") {
      if (!/^[0-9]{13}$/.test(value)) return "CNP-ul trebuie să conțină 13 cifre.";
      if (!cnpIsValid(value)) return "CNP-ul nu este valid.";
    }
    if (field === "id_series" && !/^[A-Z]{2}$/.test(value)) {
      return "Seria CI trebuie să conțină două litere.";
    }
    if (field === "id_number" && !/^[0-9]{6}$/.test(value)) {
      return "Numărul CI trebuie să conțină 6 cifre.";
    }
    if (field === "id_issued_at") {
      const match = value.match(/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/);
      if (!match) return "Data emiterii CI nu este validă.";
      const issued = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (
        Number.isNaN(issued.getTime())
        || issued.getFullYear() !== Number(match[1])
        || issued.getMonth() !== Number(match[2]) - 1
        || issued.getDate() !== Number(match[3])
        || issued > today
      ) {
        return "Data emiterii CI nu este validă.";
      }
    }
    if (field === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      return "Adresa de email nu este validă.";
    }
    if (field === "phone" && !/^\+?[0-9\s().-]{7,40}$/.test(value)) {
      return "Numărul de telefon nu este valid.";
    }

    return "";
  };

  const showFieldMessage = (input, message, force = false) => {
    const errorId = input.getAttribute("aria-describedby");
    const fieldError = errorId ? document.getElementById(errorId) : null;
    const shouldShow = message !== "" && (force || touched.has(input) || input.value.trim() !== "");
    input.setCustomValidity(message);
    if (shouldShow) {
      input.setAttribute("aria-invalid", "true");
    } else {
      input.removeAttribute("aria-invalid");
    }
    if (fieldError) {
      fieldError.textContent = shouldShow ? message : "";
      fieldError.hidden = !shouldShow;
    }
  };

  const validateField = (input, force = false) => {
    const field = input.dataset.anafValidate || "";
    const normalized = normalizeField(field, input.value);
    if (normalized !== input.value) {
      input.value = normalized;
    }
    const message = fieldMessage(input);
    showFieldMessage(input, message, force);
    return message === "";
  };

  const validateConsent = (force = false) => {
    if (!consent) return true;
    const message = consent.checked ? "" : "Confirmă acordul pentru consultarea și prelucrarea datelor prin ANAF.";
    consent.setCustomValidity(message);
    if (consentError) {
      const shouldShow = message !== "" && (force || consentTouched);
      if (shouldShow) {
        consent.setAttribute("aria-invalid", "true");
      } else {
        consent.removeAttribute("aria-invalid");
      }
      consentError.textContent = shouldShow ? message : "";
      consentError.hidden = !shouldShow;
    }
    return message === "";
  };

  const validateAll = (force = false) => {
    const fieldResults = fields.map((field) => validateField(field, force));
    const consentOk = validateConsent(force);
    return fieldResults.every(Boolean) && consentOk;
  };

  fields.forEach((field) => {
    validateField(field, false);
    field.addEventListener("input", () => {
      touched.add(field);
      validateField(field, false);
    });
    field.addEventListener("blur", () => {
      touched.add(field);
      validateField(field, true);
    });
    field.addEventListener("change", () => {
      touched.add(field);
      validateField(field, true);
    });
  });

  if (consent) {
    validateConsent(false);
    consent.addEventListener("change", () => {
      consentTouched = true;
      validateConsent(true);
    });
  }

  const applyDrawingStyle = () => {
    ctx.lineWidth = 2.25;
    ctx.lineCap = "round";
    ctx.lineJoin = "round";
    ctx.strokeStyle = "#172018";
  };

  const resetDrawingTransform = () => {
    const rect = canvas.getBoundingClientRect();
    ctx.setTransform(
      logicalWidth / Math.max(1, rect.width),
      0,
      0,
      logicalHeight / Math.max(1, rect.height),
      0,
      0
    );
    applyDrawingStyle();
  };

  const resize = () => {
    if (canvas.width !== logicalWidth) canvas.width = logicalWidth;
    if (canvas.height !== logicalHeight) canvas.height = logicalHeight;
    resetDrawingTransform();
  };

  const point = (event) => {
    const rect = canvas.getBoundingClientRect();
    return {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top
    };
  };

  const clear = () => {
    ctx.save();
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.clearRect(0, 0, logicalWidth, logicalHeight);
    ctx.restore();
    resetDrawingTransform();
    signed = false;
    last = null;
    input.value = "";
    present.value = "";
  };

  const hideError = () => {
    if (error) error.hidden = true;
  };

  resize();
  window.addEventListener("resize", resize);

  canvas.addEventListener("pointerdown", (event) => {
    event.preventDefault();
    canvas.setPointerCapture(event.pointerId);
    drawing = true;
    signed = true;
    present.value = "1";
    last = point(event);
    hideError();
  });

  canvas.addEventListener("pointermove", (event) => {
    if (!drawing || !last) return;
    event.preventDefault();
    const next = point(event);
    ctx.beginPath();
    ctx.moveTo(last.x, last.y);
    ctx.lineTo(next.x, next.y);
    ctx.stroke();
    last = next;
  });

  ["pointerup", "pointercancel", "pointerleave"].forEach((name) => {
    canvas.addEventListener(name, () => {
      drawing = false;
      last = null;
    });
  });

  if (clearButton) {
    clearButton.addEventListener("click", clear);
  }

  if (form) {
    form.addEventListener("submit", (event) => {
      if (!validateAll(true)) {
        event.preventDefault();
        event.stopImmediatePropagation();
        const firstInvalid = form.querySelector("[aria-invalid=true]");
        if (firstInvalid) firstInvalid.focus();
        return;
      }
      if (!signed) {
        event.preventDefault();
        event.stopImmediatePropagation();
        if (error) error.hidden = false;
        canvas.focus();
        return;
      }
      input.value = canvas.toDataURL("image/png");
      present.value = "1";
    }, true);
  }
})();
</script>';
}

function render_anaf_consent_form(array $site, ?array $record = null, array $errors = [], array $old = [], bool $sent = false, string $downloadToken = ''): string
{
    if (!headers_sent()) {
        header('Cache-Control: no-store, private');
    }

    $token = (string) ($old['public_token'] ?? ($record['public_token'] ?? ''));
    $values = array_merge(anaf_empty_data(), $record ? array_intersect_key($record, anaf_empty_data()) : [], $old);

    if ($sent) {
        $downloadHtml = '';
        if ($downloadToken !== '' && anaf_fetch_consent_by_pdf_download_token($downloadToken)) {
            $downloadHtml = '<p><a class="button" href="' . e(anaf_public_pdf_download_url($downloadToken)) . '">Descarcă PDF-ul completat</a></p>
      <p class="privacy">Linkul de descărcare este valabil timp de 7 zile.</p>';
        }

        $body = '<section class="page-hero consent-hero">
    <div>
      <p class="eyebrow">Acord ANAF</p>
      <h1>Acordul a fost înregistrat</h1>
      <p>Îți mulțumim. Confirmarea a fost salvată în siguranță și va fi verificată de echipa Local Capital.</p>
      ' . $downloadHtml . '
    </div>
  </section>';

        return public_layout($site, $body, [
            'title' => 'Acord ANAF înregistrat',
            'description' => 'Confirmare acord ANAF Local Capital',
            'canonicalPath' => ANAF_PUBLIC_PATH,
            'robots' => 'noindex,nofollow',
            'alternateLanguages' => false,
            'showLanguageNav' => false,
        ]);
    }

    $fields = '';
    foreach (anaf_fields() as $field => $meta) {
        $type = $field === 'email' ? 'email' : ($field === 'address' ? 'textarea' : 'text');
        $required = !empty($meta['required']) ? ' required' : '';
        $autocomplete = [
            'last_name' => 'family-name',
            'first_name' => 'given-name',
            'email' => 'email',
            'phone' => 'tel',
            'address' => 'street-address',
            'county' => 'address-level1',
            'locality' => 'address-level2',
            'street' => 'address-line1',
            'street_number' => 'address-line2',
        ][$field] ?? 'off';

        $errorId = 'anaf-' . str_replace('_', '-', $field) . '-error';
        $validationAttrs = ' data-anaf-validate="' . e($field) . '" data-anaf-label="' . e($meta['label']) . '" aria-describedby="' . e($errorId) . '"';

        if ($type === 'textarea') {
            $fields .= '<label>' . e($meta['label']) . '<textarea name="' . e($field) . '" rows="4" maxlength="' . e((string) $meta['max']) . '"' . $required . ' autocomplete="' . e($autocomplete) . '"' . $validationAttrs . '>' . e($values[$field] ?? '') . '</textarea><span class="field-error" id="' . e($errorId) . '" data-field-error hidden></span></label>';
        } else {
            $inputType = $field === 'id_issued_at' ? 'date' : ($field === 'email' ? 'email' : ($field === 'phone' ? 'tel' : 'text'));
            $fields .= '<label>' . e($meta['label']) . '<input name="' . e($field) . '" type="' . e($inputType) . '" value="' . e($values[$field] ?? '') . '" maxlength="' . e((string) $meta['max']) . '"' . $required . ' autocomplete="' . e($autocomplete) . '"' . $validationAttrs . '><span class="field-error" id="' . e($errorId) . '" data-field-error hidden></span></label>';
        }
    }

    $errorHtml = '';
    if ($errors) {
        $items = '';
        foreach ($errors as $error) {
            $items .= '<li>' . e($error) . '</li>';
        }
        $errorHtml = '<div class="form-message error"><strong>Verifică formularul:</strong><ul>' . $items . '</ul></div>';
    }

    $body = '<section class="page-hero consent-hero">
    <div>
      <p class="eyebrow">Acord ANAF</p>
      <h1>Consimțământ pentru consultarea datelor prin ANAF</h1>
      <p>Completează datele de mai jos doar dacă ai primit această solicitare de la Local Capital sau dorești să transmiți acordul pentru analiza solicitării tale.</p>
    </div>
  </section>
  <section class="consent-layout">
    <form class="admin-form consent-form" action="' . e(anaf_form_action($token)) . '" method="post" novalidate' . recaptcha_form_attributes('anaf_consent') . '>
      ' . $errorHtml . '
      <input type="hidden" name="anaf_form_token" value="' . e(contact_form_token()) . '">
      <input type="hidden" name="public_token" value="' . e($token) . '">
      ' . render_recaptcha_field('anaf_consent') . '
      <label class="hidden-field">Website <input name="company_website" tabindex="-1" autocomplete="off"></label>
      <div class="form-grid two-columns">' . $fields . '</div>
      <div class="consent-box">
        <strong>Text acord</strong>
        <p>' . nl2br(e(anaf_consent_text()), false) . '</p>
      </div>
      <label class="checkbox privacy-check"><input name="anaf_consent" type="checkbox" value="1" ' . (!empty($old['anaf_consent']) ? 'checked' : '') . ' required aria-describedby="anaf-consent-error"> Confirm acordul pentru consultarea și prelucrarea datelor prin ANAF.</label>
      <p class="field-error checkbox-error" id="anaf-consent-error" data-consent-error hidden></p>
      <div class="signature-panel" data-signature-pad>
        <div class="signature-title-row">
          <label for="anaf-signature-pad">Semnătură client</label>
          <button class="button button-secondary signature-clear" type="button" data-signature-clear>Șterge</button>
        </div>
        <div class="signature-canvas-wrap">
          <canvas id="anaf-signature-pad" width="' . ANAF_SIGNATURE_CANVAS_WIDTH . '" height="' . ANAF_SIGNATURE_CANVAS_HEIGHT . '" tabindex="0" aria-label="Semnătură client"></canvas>
        </div>
        <input type="hidden" name="signature_image" value="" data-signature-input>
        <input type="hidden" name="signature_present" value="" data-signature-present>
        <p class="form-message error signature-error" data-signature-error hidden>Semnează în chenar înainte de trimitere.</p>
        <p class="privacy">Semnează cu mouse-ul sau, pe telefon, direct cu degetul. Semnătura va fi atașată ca imagine în PDF împreună cu data, ora și IP-ul trimiterii.</p>
      </div>
      <p class="privacy">Datele sunt transmise securizat și sunt folosite doar pentru scopurile menționate. Nu trimite acest link altei persoane.</p>
      <button class="button" type="submit">Trimite acordul</button>
    </form>
    <aside class="security-note">
      <h2>Siguranța datelor</h2>
      <p>Pagina nu este indexată de motoarele de căutare. Datele sensibile sunt stocate criptat, iar trimiterea formularului este protejată cu reCAPTCHA și limitare de trafic.</p>
      <p>Poți solicita informații despre prelucrarea datelor la protectiadatelor@localcapital.ro.</p>
    </aside>
  </section>
  ' . render_anaf_signature_script();

    return public_layout($site, $body, [
        'title' => 'Acord ANAF',
        'description' => 'Formular român pentru consimțământul de consultare a datelor prin ANAF',
        'canonicalPath' => ANAF_PUBLIC_PATH,
        'robots' => 'noindex,nofollow',
        'alternateLanguages' => false,
        'showLanguageNav' => false,
        'recaptcha' => true,
    ]);
}

function save_anaf_consent_submission(?array $record, array $input): array
{
    ensure_anaf_consent_tables();

    $errors = [];
    $token = (string) ($input['public_token'] ?? '');
    $tokenHash = $token !== '' ? anaf_token_hash($token) : null;

    if (!verify_contact_form_token((string) ($input['anaf_form_token'] ?? ''))) {
        $errors[] = 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.';
    }
    if (trim((string) ($input['company_website'] ?? '')) !== '') {
        $errors[] = 'Formularul nu a putut fi trimis.';
    }
    if ($record) {
        if (anaf_status($record) !== 'draft') {
            $errors[] = 'Linkul nu mai este activ.';
        }
        if ($token === '' || !hash_equals((string) ($record['public_token_hash'] ?? ''), (string) $tokenHash)) {
            $errors[] = 'Linkul nu este valid.';
        }
    }
    if (anaf_rate_limited($tokenHash)) {
        $errors[] = 'Au fost prea multe încercări într-un timp scurt. Te rugăm să revii peste câteva minute.';
    }

    $recaptcha = recaptcha_verify('anaf_consent', (string) ($input['recaptcha_token'] ?? ''));
    if (empty($recaptcha['ok'])) {
        $errors[] = recaptcha_error_text('ro');
    }

    [$data, $fieldErrors] = anaf_normalize_input($input, true);
    $errors = array_merge($errors, $fieldErrors);
    $consent = isset($input['anaf_consent']);
    if (!$consent) {
        $errors[] = 'Confirmă acordul pentru consultarea datelor prin ANAF.';
    }
    [$signatureImage, $signatureErrors] = anaf_validate_signature_input($input);
    $errors = array_merge($errors, $signatureErrors);

    $old = $data + ['public_token' => $token, 'anaf_consent' => $consent];

    if ($errors) {
        anaf_record_attempt($tokenHash, false);
        return ['ok' => false, 'errors' => $errors, 'old' => $old];
    }

    $encrypted = [];
    foreach (array_keys(anaf_fields()) as $field) {
        $encrypted[$field . '_enc'] = anaf_encrypt_value($data[$field]);
    }
    $signatureHash = hash('sha256', $signatureImage);
    $submittedAt = date('Y-m-d H:i:s');
    $ipHash = client_ip_hash();
    $userAgentHash = anaf_user_agent_hash();
    $evidenceHash = anaf_evidence_hash($data, $signatureHash, $ipHash, $userAgentHash, $submittedAt);
    $evidenceSeal = anaf_evidence_seal($evidenceHash);
    $submittedId = 0;

    if ($record) {
        $submittedId = (int) $record['id'];
        $assignments = [
            'status = ?',
            'public_token_hash = NULL',
            'public_token_enc = NULL',
            'token_expires_at = NULL',
        ];
        $params = [
            'submitted',
        ];
        foreach (array_keys(anaf_fields()) as $field) {
            $assignments[] = $field . '_enc = ?';
            $params[] = $encrypted[$field . '_enc'];
        }
        $assignments = array_merge($assignments, [
            'consent_anaf = 1',
            'consent_text_version = ?',
            'consent_text = ?',
            'signature_image_enc = ?',
            'signature_hash = ?',
            'evidence_hash = ?',
            'evidence_seal = ?',
            'ip_address_enc = ?',
            'ip_hash = ?',
            'user_agent_hash = ?',
            'submitted_at = ?',
            'updated_at = CURRENT_TIMESTAMP',
        ]);
        $params = array_merge($params, [
            ANAF_CONSENT_VERSION,
            anaf_consent_text(),
            anaf_encrypt_value($signatureImage),
            $signatureHash,
            $evidenceHash,
            $evidenceSeal,
            anaf_encrypt_value(client_ip()),
            $ipHash,
            $userAgentHash,
            $submittedAt,
            (int) $record['id'],
            'draft',
        ]);

        $stmt = db()->prepare('UPDATE anaf_consents SET ' . implode(', ', $assignments) . ' WHERE id = ? AND status = ?');
        $stmt->execute($params);
    } else {
        $columns = ['status'];
        $placeholders = ['?'];
        $params = ['submitted'];
        foreach (array_keys(anaf_fields()) as $field) {
            $columns[] = $field . '_enc';
            $placeholders[] = '?';
            $params[] = $encrypted[$field . '_enc'];
        }
        $columns = array_merge($columns, [
            'consent_anaf',
            'consent_text_version',
            'consent_text',
            'signature_image_enc',
            'signature_hash',
            'evidence_hash',
            'evidence_seal',
            'ip_address_enc',
            'ip_hash',
            'user_agent_hash',
            'submitted_at',
        ]);
        $placeholders = array_merge($placeholders, ['1', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?']);
        $params = array_merge($params, [
            ANAF_CONSENT_VERSION,
            anaf_consent_text(),
            anaf_encrypt_value($signatureImage),
            $signatureHash,
            $evidenceHash,
            $evidenceSeal,
            anaf_encrypt_value(client_ip()),
            $ipHash,
            $userAgentHash,
            $submittedAt,
        ]);

        $stmt = db()->prepare('INSERT INTO anaf_consents (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')');
        $stmt->execute($params);
        $submittedId = (int) db()->lastInsertId();
    }

    anaf_record_attempt($tokenHash, true);
    $downloadToken = $submittedId > 0
        ? anaf_public_pdf_download_token($submittedId, time() + ANAF_PUBLIC_PDF_TOKEN_TTL, $signatureHash)
        : '';

    return ['ok' => true, 'errors' => [], 'old' => [], 'download_token' => $downloadToken];
}

function unread_anaf_consent_count(): int
{
    ensure_anaf_consent_tables();

    $stmt = db()->query("SELECT COUNT(*) FROM anaf_consents WHERE status = 'submitted'");
    return (int) $stmt->fetchColumn();
}

function render_anaf_consents_admin(array $site, array $admin): string
{
    ensure_anaf_consent_tables();

    $stmt = db()->query('SELECT * FROM anaf_consents ORDER BY status = "submitted" DESC, COALESCE(submitted_at, created_at) DESC LIMIT 200');
    $rows = array_map('anaf_decrypted_row', $stmt->fetchAll());

    $items = '';
    foreach ($rows as $row) {
        $status = anaf_status($row);
        $name = trim(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? ''));
        $items .= '<tr>
          <td><span class="status-pill status-' . e($status) . '">' . e(anaf_status_label($status)) . '</span><br><small>' . e((string) ($row['created_at'] ?? '')) . '</small></td>
          <td><strong>' . e($name ?: 'Fără nume') . '</strong><br><small>CNP ' . e(anaf_mask_cnp((string) ($row['cnp'] ?? ''))) . '</small></td>
          <td><a href="mailto:' . e($row['email'] ?? '') . '">' . e($row['email'] ?? '') . '</a><br>' . e($row['phone'] ?? '') . '</td>
          <td>' . e($row['submitted_at'] ?: '-') . '</td>
          <td><a class="button button-secondary" href="/admin/anaf-consents/' . e((string) $row['id']) . '?lang=' . e($site['language']) . '">Detalii</a></td>
        </tr>';
    }

    $body = '<section class="admin-card wide">
      <div class="admin-title-row">
        <div>
          <p class="eyebrow">Acorduri ANAF</p>
          <h1>Consimțăminte clienți</h1>
          <p>Lista include linkurile generate și acordurile acceptate. CNP-ul este mascat în listă; detaliile complete sunt disponibile doar în pagina individuală.</p>
        </div>
        <a class="button" href="/admin/anaf-consents/new?lang=' . e($site['language']) . '">Generează link</a>
      </div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Status</th><th>Client</th><th>Contact</th><th>Acceptat la</th><th>Acțiune</th></tr></thead>
          <tbody>' . ($items ?: '<tr><td colspan="5">Nu există acorduri ANAF.</td></tr>') . '</tbody>
        </table>
      </div>
    </section>';

    return admin_layout($site, $admin, $body, 'Acorduri ANAF');
}

function render_anaf_consent_create_form(array $site, array $admin, array $errors = [], array $old = []): string
{
    $values = array_merge(anaf_empty_data(), $old);
    $fields = '';
    foreach (anaf_fields() as $field => $meta) {
        if ($field === 'address') {
            $fields .= '<label>' . e($meta['label']) . '<textarea name="' . e($field) . '" rows="4" maxlength="' . e((string) $meta['max']) . '">' . e($values[$field] ?? '') . '</textarea></label>';
        } else {
            $inputType = $field === 'id_issued_at' ? 'date' : 'text';
            $fields .= '<label>' . e($meta['label']) . '<input name="' . e($field) . '" type="' . e($inputType) . '" value="' . e($values[$field] ?? '') . '" maxlength="' . e((string) $meta['max']) . '"></label>';
        }
    }

    $errorHtml = '';
    if ($errors) {
        $items = '';
        foreach ($errors as $error) {
            $items .= '<li>' . e($error) . '</li>';
        }
        $errorHtml = '<div class="form-message error"><strong>Verifică datele:</strong><ul>' . $items . '</ul></div>';
    }

    $body = '<section class="admin-card wide">
      <p class="eyebrow">Acorduri ANAF</p>
      <h1>Generează formular precompletat</h1>
      <p>Completează doar datele pe care le ai deja. Linkul generat expiră în 30 de zile și devine inutilizabil după trimiterea acordului.</p>
      <form class="admin-form" action="/admin/anaf-consents/new?lang=' . e($site['language']) . '" method="post">
        <input type="hidden" name="lang" value="' . e($site['language']) . '">
        <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
        ' . $errorHtml . '
        <div class="form-grid two-columns">' . $fields . '</div>
        <button class="button" type="submit">Generează link securizat</button>
      </form>
    </section>';

    return admin_layout($site, $admin, $body, 'Generează acord ANAF');
}

function create_anaf_consent_draft(array $site, array $admin): int
{
    ensure_anaf_consent_tables();

    [$data, $errors] = anaf_normalize_input($_POST, false);
    if ($errors) {
        http_response_code(422);
        echo render_anaf_consent_create_form($site, $admin, $errors, $data);
        exit;
    }

    $token = anaf_public_token();
    $tokenHash = anaf_token_hash($token);
    $encrypted = [];
    foreach (array_keys(anaf_fields()) as $field) {
        $encrypted[$field . '_enc'] = anaf_encrypt_value($data[$field]);
    }

    $columns = ['status', 'public_token_hash', 'public_token_enc', 'token_expires_at'];
    $placeholders = ['?', '?', '?', 'DATE_ADD(NOW(), INTERVAL 30 DAY)'];
    $params = ['draft', $tokenHash, anaf_encrypt_value($token)];
    foreach (array_keys(anaf_fields()) as $field) {
        $columns[] = $field . '_enc';
        $placeholders[] = '?';
        $params[] = $encrypted[$field . '_enc'];
    }
    $columns[] = 'created_by_admin_id';
    $placeholders[] = '?';
    $params[] = (int) $admin['id'];

    $stmt = db()->prepare('INSERT INTO anaf_consents (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')');
    $stmt->execute($params);

    return (int) db()->lastInsertId();
}

function render_anaf_consent_detail(array $site, array $admin, int $id): ?string
{
    $row = anaf_fetch_consent($id);
    if (!$row) {
        return null;
    }

    $status = anaf_status($row);
    $name = trim(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? ''));
    $link = $status === 'draft' ? anaf_public_url($row) : '';
    $linkHtml = $link !== ''
        ? '<label>Link client<input class="readonly-link" value="' . e($link) . '" readonly></label><p class="privacy">Trimite acest link doar clientului vizat. Linkul expiră la ' . e((string) $row['token_expires_at']) . '.</p>'
        : '<p class="form-message">Linkul nu mai este activ. Pentru acordurile acceptate descarcă PDF-ul.</p>';
    $pdfHtml = $status === 'submitted'
        ? '<a class="button" href="/admin/anaf-consents/' . e((string) $id) . '/pdf?lang=' . e($site['language']) . '">Descarcă PDF</a>'
        : '';

    $fields = [
        'Status' => anaf_status_label($status),
        'Număr cerere/dosar' => $row['request_reference'] ?: '-',
        'Nume' => $row['last_name'] ?? '',
        'Prenume' => $row['first_name'] ?? '',
        'CNP' => $row['cnp'] ?? '',
        'Județ CI' => $row['county'] ?? '',
        'Localitate CI' => $row['locality'] ?? '',
        'Stradă CI' => $row['street'] ?? '',
        'Nr.' => $row['street_number'] ?? '',
        'Bl.' => $row['building'] ?: '-',
        'Sc.' => $row['stair'] ?: '-',
        'Ap.' => $row['apartment'] ?: '-',
        'Serie CI' => $row['id_series'] ?? '',
        'Număr CI' => $row['id_number'] ?? '',
        'CI emis de' => $row['id_issued_by'] ?? '',
        'Data emiterii CI' => $row['id_issued_at'] ?? '',
        'Email' => $row['email'] ?? '',
        'Telefon' => $row['phone'] ?? '',
        'Adresă' => $row['address'] ?? '',
        'Acceptat la' => $row['submitted_at'] ?: '-',
        'Versiune acord' => $row['consent_text_version'] ?: '-',
        'Semnătură hash' => $row['signature_hash'] ?: '-',
        'Audit hash' => $row['evidence_hash'] ?: '-',
        'Audit seal' => $row['evidence_seal'] ?: '-',
        'IP acceptare' => $row['ip_address'] ?: '-',
        'IP hash' => $row['ip_hash'] ?: '-',
    ];
    $details = '';
    foreach ($fields as $label => $value) {
        $details .= '<div><dt>' . e($label) . '</dt><dd>' . e((string) $value) . '</dd></div>';
    }
    $signaturePreview = !empty($row['signature_image'])
        ? '<section class="admin-subsection">
        <h2>Semnătură salvată</h2>
        <div class="signature-preview"><img src="' . e($row['signature_image']) . '" alt="Semnătura clientului"></div>
      </section>'
        : '';

    $body = '<section class="admin-card wide">
      <p class="eyebrow">Acord ANAF #' . e((string) $id) . '</p>
      <h1>' . e($name ?: 'Client fără nume') . '</h1>
      <div class="admin-actions">
        <a class="button button-secondary" href="/admin/anaf-consents?lang=' . e($site['language']) . '">Înapoi la listă</a>
        ' . $pdfHtml . '
      </div>
      <dl class="detail-grid">' . $details . '</dl>
      <section class="admin-subsection">
        <h2>Link formular</h2>
        ' . $linkHtml . '
      </section>
      ' . $signaturePreview . '
      <section class="admin-subsection">
        <h2>Text acord salvat</h2>
        <p class="consent-text-preview">' . e($row['consent_text'] ?: anaf_consent_text()) . '</p>
      </section>
    </section>';

    return admin_layout($site, $admin, $body, 'Acord ANAF #' . $id);
}

function anaf_pdf_safe_text(string $value): string
{
    $value = strtr($value, [
        'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'î' => 'i', 'Î' => 'I',
        'ș' => 's', 'Ș' => 'S', 'ş' => 's', 'Ş' => 'S', 'ț' => 't', 'Ț' => 'T',
        'ţ' => 't', 'Ţ' => 'T', '–' => '-', '—' => '-', '„' => '"', '”' => '"',
    ]);
    $value = preg_replace('/[^\x20-\x7E]/', ' ', $value) ?? $value;
    return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
}

function anaf_pdf_escape(string $value): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], anaf_pdf_safe_text($value));
}

function anaf_pdf_wrap(string $line, int $width = 92): array
{
    $line = anaf_pdf_safe_text($line);
    if ($line === '') {
        return [''];
    }

    return explode("\n", wordwrap($line, $width, "\n", false));
}

function anaf_consent_pdf_document(array $row): string
{
    if (is_file(ANAF_TEMPLATE_PDF)) {
        $template = file_get_contents(ANAF_TEMPLATE_PDF);
        if (is_string($template) && str_starts_with($template, '%PDF-')) {
            try {
                return anaf_fill_template_pdf($template, $row);
            } catch (Throwable $error) {
                error_log('LOCALCAPITAL_ANAF_TEMPLATE_PDF_FAILED ' . $error->getMessage());
            }
        }
    }

    return anaf_consent_audit_pdf_document($row);
}

function anaf_filename_part(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    if (function_exists('iconv')) {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (is_string($ascii) && $ascii !== '') {
            $value = $ascii;
        }
    }

    $value = strtolower(anaf_pdf_safe_text($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

    return trim($value, '-');
}

function anaf_pdf_download_filename(array $row): string
{
    $name = trim(implode('-', array_filter([
        anaf_filename_part((string) ($row['last_name'] ?? '')),
        anaf_filename_part((string) ($row['first_name'] ?? '')),
    ], static fn (string $part): bool => $part !== '')), '-');
    if ($name === '') {
        $name = 'client';
    }

    $cnp = preg_replace('/\D+/', '', (string) ($row['cnp'] ?? '')) ?? '';
    $cnpSuffix = strlen($cnp) >= 6 ? substr($cnp, -6) : 'necunoscut';

    return 'acord-anaf-' . $name . '-cnp-' . $cnpSuffix . '.pdf';
}

function anaf_consent_audit_pdf_document(array $row): string
{
    $name = trim(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? ''));
    $lines = [
        'ACORD CONSULTARE DATE PRIN ANAF',
        'LOCAL CAPITAL IFN S.A.',
        '',
        'Document generat pe baza sablonului ACORD ANAF.',
        '',
        'Numar cerere/dosar: ' . ($row['request_reference'] ?: '-'),
        'Client: ' . $name,
        'CNP: ' . ($row['cnp'] ?? ''),
        'Adresa CI: ' . anaf_compose_address($row),
        'Act identitate: ' . ($row['id_series'] ?? '') . ' ' . ($row['id_number'] ?? '') . ', emis de ' . ($row['id_issued_by'] ?? '') . ', la data ' . ($row['id_issued_at'] ?? ''),
        'Email: ' . ($row['email'] ?? ''),
        'Telefon: ' . ($row['phone'] ?? ''),
        'Adresa: ' . ($row['address'] ?? ''),
        '',
        'Acord acceptat la: ' . ($row['submitted_at'] ?? ''),
        'Acceptare electronica: ' . $name . ', data/ora ' . ($row['submitted_at'] ?? '') . ', IP ' . ($row['ip_address'] ?? ''),
        'Versiune text: ' . ($row['consent_text_version'] ?? ANAF_CONSENT_VERSION),
        'Audit hash: ' . ($row['evidence_hash'] ?? ''),
        'Audit seal: ' . ($row['evidence_seal'] ?? ''),
        '',
        'Text acord:',
    ];
    foreach (anaf_pdf_wrap($row['consent_text'] ?: anaf_consent_text(), 94) as $line) {
        $lines[] = $line;
    }
    $lines[] = '';
    $lines[] = 'Confirmare: clientul a bifat caseta de acord in formularul online.';
    $lines[] = 'Audit: ip_hash=' . ($row['ip_hash'] ?? '') . '; user_agent_hash=' . ($row['user_agent_hash'] ?? '');
    $lines[] = '';
    $lines[] = 'Document generat din panoul admin Local Capital.';

    $wrapped = [];
    foreach ($lines as $line) {
        foreach (anaf_pdf_wrap($line, 98) as $wrappedLine) {
            $wrapped[] = $wrappedLine;
        }
    }

    return anaf_simple_pdf($wrapped);
}

function anaf_pdf_num(float $value): string
{
    $formatted = number_format($value, 3, '.', '');
    return rtrim(rtrim($formatted, '0'), '.') ?: '0';
}

function anaf_pdf_hex_string(string $value): string
{
    if ($value === '') {
        return '()';
    }

    $encoded = function_exists('mb_convert_encoding')
        ? mb_convert_encoding($value, 'UTF-16BE', 'UTF-8')
        : iconv('UTF-8', 'UTF-16BE//IGNORE', $value);
    if (!is_string($encoded)) {
        $encoded = anaf_pdf_safe_text($value);
    }

    return '<FEFF' . strtoupper(bin2hex($encoded)) . '>';
}

function anaf_pdf_stream_object(string $stream, string $dictionary = ''): string
{
    $dictionary = trim($dictionary);
    if ($dictionary === '') {
        $dictionary = '/Length ' . strlen($stream);
    } else {
        $dictionary .= ' /Length ' . strlen($stream);
    }

    return '<< ' . $dictionary . " >>\nstream\n" . $stream . "\nendstream";
}

function anaf_pdf_trim_to_box(string $value, float $width, float $fontSize): string
{
    $value = anaf_pdf_safe_text($value);
    $max = max(3, (int) floor($width / max(1.0, $fontSize * 0.52)));
    if (strlen($value) <= $max) {
        return $value;
    }

    return rtrim(substr($value, 0, max(1, $max - 3))) . '...';
}

function anaf_pdf_form_appearance(string $value, float $width, float $height, float $fontSize = 9.0, string $align = 'left'): string
{
    $text = anaf_pdf_trim_to_box($value, $width - 4.0, $fontSize);
    $x = 2.0;
    if ($align === 'center') {
        $estimatedWidth = strlen($text) * $fontSize * 0.48;
        $x = max(2.0, ($width - $estimatedWidth) / 2.0);
    }
    $y = max(2.0, ($height - $fontSize) / 2.0);
    $stream = "q\n1 1 1 rg\n0 0 " . anaf_pdf_num($width) . ' ' . anaf_pdf_num($height) . " re\nf\nQ\n";
    $stream .= "BT\n/Helv " . anaf_pdf_num($fontSize) . " Tf\n0 g\n1 0 0 1 " . anaf_pdf_num($x) . ' ' . anaf_pdf_num($y) . " Tm\n(" . anaf_pdf_escape($text) . ") Tj\nET";

    return anaf_pdf_stream_object($stream, '/Type /XObject /Subtype /Form /FormType 1 /BBox[0 0 ' . anaf_pdf_num($width) . ' ' . anaf_pdf_num($height) . '] /Resources << /Font << /Helv 22 0 R >> >>');
}

function anaf_pdf_template_fields(): array
{
    return [
        'Nume' => ['object' => 191, 'rect' => [92.76, 642.6, 280.68, 661.56], 'page' => 166, 'font' => 10.0],
        'Prenume' => ['object' => 192, 'rect' => [369.194, 642.6, 571.051, 661.56], 'page' => 166, 'font' => 10.0],
        'Judet_CI' => ['object' => 193, 'rect' => [94.1076, 621.84, 241.2, 640.8], 'page' => 166, 'font' => 9.5],
        'Localitate_CI' => ['object' => 194, 'rect' => [324.264, 621.84, 571.426, 640.8], 'page' => 166, 'font' => 9.5],
        'Strada_CI' => ['object' => 195, 'rect' => [94.4827, 600.96, 241.2, 620.04], 'page' => 166, 'font' => 9.0],
        'Nr_CI' => ['object' => 196, 'rect' => [281.16, 600.96, 335.982, 620.04], 'page' => 166, 'font' => 9.0, 'align' => 'center'],
        'Bloc_CI' => ['object' => 197, 'rect' => [356.8, 601.96, 399.898, 621.04], 'page' => 166, 'font' => 9.0, 'align' => 'center'],
        'Scara_CI' => ['object' => 198, 'rect' => [429.56, 601.96, 484.586, 621.04], 'page' => 166, 'font' => 9.0, 'align' => 'center'],
        'Apartament_CI' => ['object' => 199, 'rect' => [514.706, 600.96, 570.3, 621.54], 'page' => 166, 'font' => 9.0, 'align' => 'center'],
        'Serie_CI' => ['object' => 200, 'rect' => [229.8, 580.2, 261.24, 599.16], 'page' => 166, 'font' => 9.0, 'align' => 'center'],
        'Numar_CI' => ['object' => 201, 'rect' => [313.44, 580.2, 385.901, 599.16], 'page' => 166, 'font' => 9.0, 'align' => 'center'],
        'CNP' => ['object' => 202, 'rect' => [428.561, 580.2, 571.051, 599.16], 'page' => 166, 'font' => 9.0],
        'CI_Eliberat' => ['object' => 203, 'rect' => [92.2321, 559.065, 235.32, 578.025], 'page' => 166, 'font' => 8.5],
        'Data_Elib_CI' => ['object' => 204, 'rect' => [313.91, 559.44, 570.675, 578.4], 'page' => 166, 'font' => 9.0],
    ];
}

function anaf_pdf_field_object(array $field, string $name, string $value, int $appearanceObject): string
{
    [$x1, $y1, $x2, $y2] = $field['rect'];
    $rect = implode(' ', array_map(static fn (float $number): string => anaf_pdf_num($number), $field['rect']));
    $q = ($field['align'] ?? '') === 'center' ? '/Q 1' : '';

    return '<</AP<</N ' . $appearanceObject . ' 0 R>>/DA(/Helv ' . anaf_pdf_num((float) ($field['font'] ?? 9.0)) . ' Tf 0 g)/F 132/Ff 1/FT/Tx/MK<<>>/P ' . (int) $field['page'] . ' 0 R' . $q . '/Rect[' . $rect . ']/Subtype/Widget/T(' . $name . ')/Type/Annot/V' . anaf_pdf_hex_string($value) . '>>';
}

function anaf_pdf_field_values(array $row): array
{
    return [
        'Nume' => (string) ($row['last_name'] ?? ''),
        'Prenume' => (string) ($row['first_name'] ?? ''),
        'Judet_CI' => (string) ($row['county'] ?? ''),
        'Localitate_CI' => (string) ($row['locality'] ?? ''),
        'Strada_CI' => (string) ($row['street'] ?? ''),
        'Nr_CI' => (string) ($row['street_number'] ?? ''),
        'Bloc_CI' => (string) ($row['building'] ?? ''),
        'Scara_CI' => (string) ($row['stair'] ?? ''),
        'Apartament_CI' => (string) ($row['apartment'] ?? ''),
        'Serie_CI' => (string) ($row['id_series'] ?? ''),
        'Numar_CI' => (string) ($row['id_number'] ?? ''),
        'CNP' => (string) ($row['cnp'] ?? ''),
        'CI_Eliberat' => (string) ($row['id_issued_by'] ?? ''),
        'Data_Elib_CI' => anaf_pdf_ro_date((string) ($row['id_issued_at'] ?? '')),
        'DataInch' => anaf_pdf_ro_date((string) ($row['submitted_at'] ?? '')),
    ];
}

function anaf_pdf_ro_date(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return (new DateTimeImmutable('now'))->format('d.m.Y');
    }

    try {
        return (new DateTimeImmutable($value))->format('d.m.Y');
    } catch (Throwable) {
        return $value;
    }
}

function anaf_pdf_timestamp(array $row): string
{
    $value = trim((string) ($row['submitted_at'] ?? ''));
    try {
        return (new DateTimeImmutable($value !== '' ? $value : 'now'))->format('Y-m-d H:i:s');
    } catch (Throwable) {
        return $value !== '' ? $value : date('Y-m-d H:i:s');
    }
}

function anaf_signature_ink_bounds($source, int $width, int $height): ?array
{
    $minX = $width;
    $minY = $height;
    $maxX = -1;
    $maxY = -1;

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $color = imagecolorat($source, $x, $y);
            $alpha = ($color >> 24) & 0x7F;
            if ($alpha > 120) {
                continue;
            }
            $red = ($color >> 16) & 0xFF;
            $green = ($color >> 8) & 0xFF;
            $blue = $color & 0xFF;
            if ($red > 245 && $green > 245 && $blue > 245) {
                continue;
            }

            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x);
            $maxY = max($maxY, $y);
        }
    }

    return $maxX >= 0 ? [$minX, $minY, $maxX, $maxY] : null;
}

function anaf_signature_jpeg_from_data_url(string $dataUrl): ?array
{
    if (!function_exists('imagecreatefromstring') || !preg_match('#^data:image/png;base64,([A-Za-z0-9+/=\s]+)$#', $dataUrl, $match)) {
        return null;
    }

    $raw = base64_decode(preg_replace('/\s+/', '', $match[1]) ?? '', true);
    if (!is_string($raw)) {
        return null;
    }

    $source = @imagecreatefromstring($raw);
    if (!$source) {
        return null;
    }

    $width = imagesx($source);
    $height = imagesy($source);
    $bounds = anaf_signature_ink_bounds($source, $width, $height);
    if (!$bounds) {
        return null;
    }

    $canvas = imagecreatetruecolor($width, $height);
    if (!$canvas) {
        return null;
    }
    imagealphablending($canvas, true);
    $white = imagecolorallocate($canvas, 255, 255, 255);
    imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
    imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

    ob_start();
    imagejpeg($canvas, null, 92);
    $jpeg = ob_get_clean();

    if (!is_string($jpeg) || $jpeg === '') {
        return null;
    }

    return ['width' => $width, 'height' => $height, 'data' => $jpeg];
}

function anaf_pdf_image_xobject(array $image): string
{
    return anaf_pdf_stream_object((string) $image['data'], '/Type /XObject /Subtype /Image /Width ' . (int) $image['width'] . ' /Height ' . (int) $image['height'] . ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode');
}

function anaf_pdf_signature_appearance(?array $image, ?int $imageObject, float $width, float $height): string
{
    $stream = "q\n1 1 1 rg\n0 0 " . anaf_pdf_num($width) . ' ' . anaf_pdf_num($height) . " re\nf\nQ\n";
    $resources = '/Font << /Helv 22 0 R >>';
    if ($image && $imageObject !== null) {
        $imageRatio = max(0.1, (float) $image['width'] / max(1, (float) $image['height']));
        $boxRatio = $width / max(1.0, $height);
        if ($imageRatio > $boxRatio) {
            $drawWidth = $width - 4.0;
            $drawHeight = $drawWidth / $imageRatio;
        } else {
            $drawHeight = $height - 4.0;
            $drawWidth = $drawHeight * $imageRatio;
        }
        $x = max(2.0, ($width - $drawWidth) / 2.0);
        $y = max(2.0, ($height - $drawHeight) / 2.0);
        $stream .= "q\n" . anaf_pdf_num($drawWidth) . ' 0 0 ' . anaf_pdf_num($drawHeight) . ' ' . anaf_pdf_num($x) . ' ' . anaf_pdf_num($y) . " cm\n/SigImg Do\nQ\n";
        $resources .= ' /XObject << /SigImg ' . $imageObject . ' 0 R >>';
    } else {
        $stream .= "BT\n/Helv 8 Tf\n0 g\n1 0 0 1 4 12 Tm\n(Semnatura indisponibila) Tj\nET\n";
    }

    return anaf_pdf_stream_object($stream, '/Type /XObject /Subtype /Form /FormType 1 /BBox[0 0 ' . anaf_pdf_num($width) . ' ' . anaf_pdf_num($height) . '] /Resources << ' . $resources . ' >>');
}

function anaf_pdf_text_overlay_commands(string $value, array $rect, float $fontSize = 9.0, string $align = 'left'): string
{
    [$x1, $y1, $x2, $y2] = $rect;
    $width = $x2 - $x1;
    $height = $y2 - $y1;
    $text = anaf_pdf_trim_to_box($value, $width - 4.0, $fontSize);
    $x = $x1 + 2.0;
    if ($align === 'center') {
        $estimatedWidth = strlen($text) * $fontSize * 0.48;
        $x = $x1 + max(2.0, ($width - $estimatedWidth) / 2.0);
    }
    $y = $y1 + max(2.0, ($height - $fontSize) / 2.0);

    $stream = "q\n1 1 1 rg\n" . anaf_pdf_num($x1) . ' ' . anaf_pdf_num($y1) . ' ' . anaf_pdf_num($width) . ' ' . anaf_pdf_num($height) . " re\nf\nQ\n";
    $stream .= "BT\n/F1 " . anaf_pdf_num($fontSize) . " Tf\n0 g\n1 0 0 1 " . anaf_pdf_num($x) . ' ' . anaf_pdf_num($y) . " Tm\n(" . anaf_pdf_escape($text) . ") Tj\nET\n";

    return $stream;
}

function anaf_pdf_signature_overlay_commands(?array $image, ?int $imageObject, array $rect): string
{
    [$x1, $y1, $x2, $y2] = $rect;
    $width = $x2 - $x1;
    $height = $y2 - $y1;
    $stream = "q\n1 1 1 rg\n" . anaf_pdf_num($x1) . ' ' . anaf_pdf_num($y1) . ' ' . anaf_pdf_num($width) . ' ' . anaf_pdf_num($height) . " re\nf\nQ\n";

    if ($image && $imageObject !== null) {
        $imageRatio = max(0.1, (float) $image['width'] / max(1, (float) $image['height']));
        $boxRatio = $width / max(1.0, $height);
        if ($imageRatio > $boxRatio) {
            $drawWidth = $width - 4.0;
            $drawHeight = $drawWidth / $imageRatio;
        } else {
            $drawHeight = $height - 4.0;
            $drawWidth = $drawHeight * $imageRatio;
        }
        $x = $x1 + max(2.0, ($width - $drawWidth) / 2.0);
        $y = $y1 + max(2.0, ($height - $drawHeight) / 2.0);

        return $stream . "q\n" . anaf_pdf_num($drawWidth) . ' 0 0 ' . anaf_pdf_num($drawHeight) . ' ' . anaf_pdf_num($x) . ' ' . anaf_pdf_num($y) . " cm\n/SigImg Do\nQ\n";
    }

    return $stream . "BT\n/F1 8 Tf\n0 g\n1 0 0 1 " . anaf_pdf_num($x1 + 4.0) . ' ' . anaf_pdf_num($y1 + 12.0) . " Tm\n(Semnatura indisponibila) Tj\nET\n";
}

function anaf_pdf_data_widget_object(int $appearanceObject, int $pageObject, array $rect): string
{
    return '<</AP<</N ' . $appearanceObject . ' 0 R>>/F 132/MK<<>>/P ' . $pageObject . ' 0 R/Parent 28 0 R/Rect[' . implode(' ', array_map(static fn (float $number): string => anaf_pdf_num($number), $rect)) . ']/Subtype/Widget/Type/Annot>>';
}

function anaf_pdf_page_one_overlay_stream(array $values): string
{
    $stream = '';
    foreach (anaf_pdf_template_fields() as $name => $field) {
        $stream .= anaf_pdf_text_overlay_commands(
            (string) ($values[$name] ?? ''),
            $field['rect'],
            (float) ($field['font'] ?? 9.0),
            (string) ($field['align'] ?? 'left')
        );
    }
    $stream .= anaf_pdf_text_overlay_commands((string) ($values['DataInch'] ?? ''), [348.594, 718.268, 435.474, 733.028], 8.0, 'center');

    return anaf_pdf_stream_object($stream);
}

function anaf_pdf_page_two_overlay_stream(array $row, string $dateValue, ?array $signatureImage, ?int $signatureImageObject, array $signatureRect): string
{
    $stream = anaf_pdf_text_overlay_commands($dateValue, [54.0, 339.84, 140.88, 354.6], 8.0, 'center');
    $stream .= anaf_pdf_signature_overlay_commands($signatureImage, $signatureImageObject, $signatureRect);
    $stream .= anaf_pdf_audit_commands($row);

    return anaf_pdf_stream_object($stream);
}

function anaf_pdf_page_one_object(int $overlayContentObject): string
{
    return '<</Contents[171 0 R 172 0 R 174 0 R 175 0 R 176 0 R 177 0 R 178 0 R 179 0 R ' . $overlayContentObject . ' 0 R]/CropBox[0 0 596.04 842.52]/Group<</CS/DeviceRGB/S/Transparency/Type/Group>>/MediaBox[0 0 596.04 842.52]/Parent 162 0 R/Resources<</ExtGState<</GS10 206 0 R/GS7 207 0 R>>/Font<</F1 210 0 R/F2 213 0 R/F3 219 0 R/F4 222 0 R/F5 228 0 R/F6 231 0 R>>/ProcSet[/PDF/Text/ImageB/ImageC/ImageI]/XObject<</Image27 184 0 R/Image5 186 0 R>>>>/Rotate 0/StructParents 0/Tabs/S/Type/Page>>';
}

function anaf_pdf_page_two_object(int $overlayContentObject, ?int $signatureImageObject): string
{
    $signatureResource = $signatureImageObject !== null ? ' /SigImg ' . $signatureImageObject . ' 0 R' : '';

    return '<</Contents[2 0 R ' . $overlayContentObject . ' 0 R]/CropBox[0 0 596.04 842.52]/Group<</CS/DeviceRGB/S/Transparency/Type/Group>>/MediaBox[0 0 596.04 842.52]/Parent 162 0 R/Resources<</ExtGState<</GS10 206 0 R/GS7 207 0 R>>/Font<</F1 210 0 R/F2 213 0 R/F4 222 0 R/F5 228 0 R/F6 231 0 R>>/ProcSet[/PDF/Text/ImageB/ImageC/ImageI]/XObject<</Image31 4 0 R/Image33 6 0 R/Image35 8 0 R' . $signatureResource . '>>>>/Rotate 0/StructParents 1/Tabs/S/Type/Page>>';
}

function anaf_pdf_audit_commands(array $row): string
{
    $timestamp = anaf_pdf_timestamp($row);
    $name = trim((string) ($row['last_name'] ?? '') . ' ' . (string) ($row['first_name'] ?? ''));
    $ip = (string) ($row['ip_address'] ?? '');
    $hash = (string) ($row['signature_hash'] ?? '');
    $evidenceHash = (string) ($row['evidence_hash'] ?? '');
    $evidenceSeal = (string) ($row['evidence_seal'] ?? '');
    $stream = "q\nBT\n/F1 12 Tf\n0 g\n1 0 0 1 181 744 Tm\n(X) Tj\nET\n";
    $stream .= "BT\n/F1 7.0 Tf\n0 g\n1 0 0 1 392 296 Tm\n(Semnatar: " . anaf_pdf_escape($name !== '' ? $name : '-') . ") Tj\nET\n";
    $stream .= "BT\n/F1 7.0 Tf\n0 g\n1 0 0 1 392 286 Tm\n(Acceptat la: " . anaf_pdf_escape($timestamp) . ") Tj\nET\n";
    $stream .= "BT\n/F1 7.0 Tf\n0 g\n1 0 0 1 392 276 Tm\n(IP trimitere: " . anaf_pdf_escape($ip !== '' ? $ip : '-') . ") Tj\nET\n";
    if ($hash !== '') {
        $stream .= "BT\n/F1 6.0 Tf\n0 g\n1 0 0 1 392 266 Tm\n(Semnatura hash: " . anaf_pdf_escape(substr($hash, 0, 24)) . "...) Tj\nET\n";
    }
    if ($evidenceHash !== '') {
        $stream .= "BT\n/F1 6.0 Tf\n0 g\n1 0 0 1 392 258 Tm\n(Audit hash: " . anaf_pdf_escape(substr($evidenceHash, 0, 24)) . "...) Tj\nET\n";
    }
    if ($evidenceSeal !== '') {
        $stream .= "BT\n/F1 6.0 Tf\n0 g\n1 0 0 1 392 250 Tm\n(Audit seal: " . anaf_pdf_escape(substr($evidenceSeal, 0, 24)) . "...) Tj\nET\n";
    }
    $stream .= "Q";

    return $stream;
}

function anaf_pdf_audit_stream(array $row): string
{
    return anaf_pdf_stream_object(anaf_pdf_audit_commands($row));
}

function anaf_fill_template_pdf(string $template, array $row): string
{
    $objects = [];
    $nextObject = anaf_pdf_template_next_object($template);
    $values = anaf_pdf_field_values($row);

    foreach (anaf_pdf_template_fields() as $name => $field) {
        [$x1, $y1, $x2, $y2] = $field['rect'];
        $width = $x2 - $x1;
        $height = $y2 - $y1;
        $appearanceObject = $nextObject++;
        $objects[$appearanceObject] = anaf_pdf_form_appearance((string) ($values[$name] ?? ''), $width, $height, (float) ($field['font'] ?? 9.0), (string) ($field['align'] ?? 'left'));
        $objects[(int) $field['object']] = anaf_pdf_field_object($field, $name, (string) ($values[$name] ?? ''), $appearanceObject);
    }

    $dateValue = (string) ($values['DataInch'] ?? '');
    $dateTopAppearance = $nextObject++;
    $objects[$dateTopAppearance] = anaf_pdf_form_appearance($dateValue, 86.88, 14.76, 8.0, 'center');
    $dateBottomAppearance = $nextObject++;
    $objects[$dateBottomAppearance] = anaf_pdf_form_appearance($dateValue, 86.88, 14.76, 8.0, 'center');
    $objects[28] = '<</FT/Tx/Ff 1/Kids[205 0 R 25 0 R]/Q 1/T(DataInch)/V' . anaf_pdf_hex_string($dateValue) . '>>';
    $objects[205] = anaf_pdf_data_widget_object($dateTopAppearance, 166, [348.594, 718.268, 435.474, 733.028]);
    $objects[25] = anaf_pdf_data_widget_object($dateBottomAppearance, 1, [54.0, 339.84, 140.88, 354.6]);

    $signatureRect = [392.849, 307.24, 542.849, 339.24];
    $signatureWidth = $signatureRect[2] - $signatureRect[0];
    $signatureHeight = $signatureRect[3] - $signatureRect[1];
    $signatureImage = anaf_signature_jpeg_from_data_url((string) ($row['signature_image'] ?? ''));
    $signatureImageObject = null;
    if ($signatureImage) {
        $signatureImageObject = $nextObject++;
        $objects[$signatureImageObject] = anaf_pdf_image_xobject($signatureImage);
    }
    $signatureAppearanceObject = $nextObject++;
    $objects[$signatureAppearanceObject] = anaf_pdf_signature_appearance($signatureImage, $signatureImageObject, $signatureWidth, $signatureHeight);
    $objects[23] = '<</AP<</N ' . $signatureAppearanceObject . ' 0 R>>/F 132/Ff 1/FT/Tx/MK<<>>/P 1 0 R/Rect[' . implode(' ', array_map(static fn (float $number): string => anaf_pdf_num($number), $signatureRect)) . ']/Subtype/Widget/T(signature_1)/Type/Annot>>';

    $pageOneOverlayObject = $nextObject++;
    $objects[$pageOneOverlayObject] = anaf_pdf_page_one_overlay_stream($values);
    $pageTwoOverlayObject = $nextObject++;
    $objects[$pageTwoOverlayObject] = anaf_pdf_page_two_overlay_stream($row, $dateValue, $signatureImage, $signatureImageObject, $signatureRect);
    $objects[166] = anaf_pdf_page_one_object($pageOneOverlayObject);
    $objects[1] = anaf_pdf_page_two_object($pageTwoOverlayObject, $signatureImageObject);
    $objects[188] = '<</DA(/Helv 0 Tf 0 g )/DR<</Encoding<</PDFDocEncoding 27 0 R>>/Font<</Helv 22 0 R/ZaDb 29 0 R>>>>/Fields[]/NeedAppearances false>>';

    return anaf_pdf_append_incremental_update($template, $objects);
}

function anaf_pdf_template_next_object(string $pdf): int
{
    if (preg_match_all('/\/Size\s+([0-9]+)/', $pdf, $matches) && $matches[1]) {
        return max(array_map('intval', $matches[1]));
    }

    if (preg_match_all('/(?:^|\s)([0-9]+)\s+0\s+obj\b/', $pdf, $objects) && $objects[1]) {
        return max(array_map('intval', $objects[1])) + 1;
    }

    return 1;
}

function anaf_pdf_previous_startxref(string $pdf): int
{
    if (preg_match_all('/startxref\s+([0-9]+)/', $pdf, $matches) && $matches[1]) {
        return (int) end($matches[1]);
    }

    throw new RuntimeException('Template PDF startxref is missing.');
}

function anaf_pdf_append_incremental_update(string $pdf, array $objects): string
{
    ksort($objects, SORT_NUMERIC);
    $update = "\n";
    $offsets = [];
    foreach ($objects as $number => $object) {
        $number = (int) $number;
        $offsets[$number] = strlen($pdf) + strlen($update);
        $update .= $number . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefObject = max(max(array_keys($offsets)) + 1, anaf_pdf_template_next_object($pdf));
    $offsets[$xrefObject] = strlen($pdf) + strlen($update);
    $numbers = array_keys($offsets);
    sort($numbers, SORT_NUMERIC);
    array_unshift($numbers, 0);

    $indexParts = [];
    $stream = '';
    for ($i = 0; $i < count($numbers);) {
        $start = $numbers[$i];
        $group = [$start];
        $i++;
        while ($i < count($numbers) && $numbers[$i] === end($group) + 1) {
            $group[] = $numbers[$i];
            $i++;
        }
        $indexParts[] = $start . ' ' . count($group);
        foreach ($group as $number) {
            if ($number === 0) {
                $stream .= pack('C', 0) . pack('N', 0) . pack('n', 65535);
            } else {
                $stream .= pack('C', 1) . pack('N', $offsets[$number]) . pack('n', 0);
            }
        }
    }

    $size = max(anaf_pdf_template_next_object($pdf), max($numbers) + 1);
    $dictionary = '/Type /XRef /Size ' . $size . ' /Root 165 0 R /Info 163 0 R /Prev ' . anaf_pdf_previous_startxref($pdf) . ' /W[1 4 2] /Index[' . implode(' ', $indexParts) . ']';
    $update .= $xrefObject . " 0 obj\n" . anaf_pdf_stream_object($stream, $dictionary) . "\nendobj\nstartxref\n" . $offsets[$xrefObject] . "\n%%EOF";

    return $pdf . $update;
}

function anaf_simple_pdf(array $lines): string
{
    $pages = array_chunk($lines, 42);
    $objects = [];
    $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

    $kids = [];
    $next = 4;
    foreach ($pages as $pageLines) {
        $pageObject = $next++;
        $contentObject = $next++;
        $kids[] = $pageObject . ' 0 R';
        $stream = "BT\n/F1 11 Tf\n50 792 Td\n14 TL\n";
        foreach ($pageLines as $line) {
            $stream .= '(' . anaf_pdf_escape((string) $line) . ") Tj\nT*\n";
        }
        $stream .= "ET\n";
        $objects[$contentObject] = '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . 'endstream';
        $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents ' . $contentObject . ' 0 R >>';
    }
    $objects[2] = '<< /Type /Pages /Count ' . count($kids) . ' /Kids [' . implode(' ', $kids) . '] >>';
    ksort($objects);

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $number => $object) {
        $offsets[$number] = strlen($pdf);
        $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

    return $pdf;
}

function output_anaf_consent_pdf(int $id): void
{
    $row = anaf_fetch_consent($id);
    if (!$row || anaf_status($row) !== 'submitted') {
        http_response_code(404);
        echo render_error_page(error_page_text(admin_language(), 'not_found_title'), error_page_text(admin_language(), 'not_found_message'), admin_language());
        return;
    }

    $pdf = anaf_consent_pdf_document($row);
    $filename = anaf_pdf_download_filename($row);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('X-Document-SHA256: ' . hash('sha256', $pdf));
    header('Cache-Control: no-store, no-cache, must-revalidate, private, max-age=0', true);
    header('Pragma: no-cache', true);
    header('Expires: 0', true);
    echo $pdf;
}

function output_public_anaf_consent_pdf(string $token): void
{
    $row = anaf_fetch_consent_by_pdf_download_token($token);
    if (!$row) {
        http_response_code(404);
        echo render_error_page('Link indisponibil', 'Linkul de descărcare nu este valid sau a expirat.', DEFAULT_LANGUAGE);
        return;
    }

    $pdf = anaf_consent_pdf_document($row);
    $filename = anaf_pdf_download_filename($row);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('X-Document-SHA256: ' . hash('sha256', $pdf));
    header('Cache-Control: no-store, no-cache, must-revalidate, private, max-age=0', true);
    header('Pragma: no-cache', true);
    header('Expires: 0', true);
    echo $pdf;
}
