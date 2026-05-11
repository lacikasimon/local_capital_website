<?php

declare(strict_types=1);

const ANAF_CONSENT_VERSION = 'anaf-consent-v2-2026-05-11';
const ANAF_PUBLIC_PATH = '/acord-anaf';

function anaf_consent_text(): string
{
    return trim(<<<'TEXT'
EXEMPLU
Document de lucru pentru validare juridică și operațională înainte de utilizarea în relația cu clienții.

ACORD de consultare, transmitere și prelucrare a informațiilor din bazele de date ale Ministerului Finanțelor / Agenției Naționale de Administrare Fiscală, precum și a datelor cu caracter personal aparținând clientului

Subsemnatul/Subsemnata, în calitate de client sau solicitant al serviciilor LOCAL CAPITAL IFN S.A., confirm că am fost informat(ă) că LOCAL CAPITAL IFN S.A., în calitate de instituție financiară nebancară și operator/destinatar al datelor, dorește să consulte și să prelucreze, pentru o durată maximă de 5 zile lucrătoare de la data semnării/acceptării prezentului acord, informațiile înregistrate pe numele meu în evidențele Ministerului Finanțelor / Agenției Naționale de Administrare Fiscală.

Scopurile consultării și prelucrării sunt: inițierea sau derularea relației contractuale specifice activității de creditare, analiza eligibilității și a capacității de rambursare, administrarea riscurilor, îndeplinirea obligațiilor legale aplicabile și realizarea intereselor legitime ale LOCAL CAPITAL IFN S.A. în legătură cu solicitarea de credit.

Datele care pot fi consultate, transmise sau prelucrate, în limitele permise de lege și de protocolul aplicabil cu ANAF, pot include: nume și prenume, domiciliu/reședință, CNP/NIF/CIF, seria și numărul actului de identitate, date fiscale, venituri realizate, contribuții declarate, informații relevante pentru verificarea situației fiscale și a capacității de plată, precum și date privind cererea sau dosarul de credit.

Îmi exprim acordul expres ca LOCAL CAPITAL IFN S.A. să transmită către ANAF datele mele de identificare și datele necesare pentru efectuarea consultării, iar ANAF să transmită către LOCAL CAPITAL IFN S.A. informațiile disponibile care sunt necesare scopurilor menționate mai sus.

Înțeleg că refuzul sau retragerea acordului poate împiedica analizarea solicitării sau poate face necesară prezentarea documentelor justificative prin alte mijloace. Retragerea acordului nu afectează legalitatea prelucrărilor efectuate anterior retragerii.

Am fost informat(ă) că prezentul acord trebuie corelat cu protocolul și modelul-cadru aplicabil colaborării cu ANAF, inclusiv, după caz, OPANAF nr. 3194/2019 și OPANAF nr. 146/2022, și că datele sunt prelucrate conform Regulamentului (UE) 2016/679, Legii nr. 190/2018 și legislației aplicabile activității de creditare. Beneficiez de dreptul la informare, acces, rectificare, ștergere, restricționare, opoziție, portabilitate, dreptul de a nu face obiectul unei decizii bazate exclusiv pe prelucrare automată, precum și dreptul de a depune plângere la ANSPDCP.

Confirm că datele completate în formular sunt corecte, că am citit informarea privind prelucrarea datelor personale publicată de LOCAL CAPITAL IFN S.A. și că bifez în mod liber, specific, informat și neechivoc caseta de acord.
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
        'id_issued_by_enc' => ['definition' => 'TEXT NULL', 'after' => 'id_number_enc'],
        'id_issued_at_enc' => ['definition' => 'TEXT NULL', 'after' => 'id_issued_by_enc'],
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
        'id_series' => ['label' => 'Serie CI', 'max' => 2, 'required' => true],
        'id_number' => ['label' => 'Număr CI', 'max' => 6, 'required' => true],
        'id_issued_by' => ['label' => 'CI emis de', 'max' => 120, 'required' => true],
        'id_issued_at' => ['label' => 'Data emiterii CI', 'max' => 10, 'required' => true],
        'email' => ['label' => 'Email', 'max' => 190, 'required' => true],
        'phone' => ['label' => 'Telefon', 'max' => 40, 'required' => true],
        'address' => ['label' => 'Adresă completă', 'max' => 600, 'required' => true],
    ];
}

function anaf_empty_data(): array
{
    return array_fill_keys(array_keys(anaf_fields()), '');
}

function anaf_decrypted_row(array $row): array
{
    foreach (array_keys(anaf_fields()) as $field) {
        $row[$field] = repair_text_encoding(anaf_decrypt_value($row[$field . '_enc'] ?? ''));
    }
    $row['public_token'] = anaf_decrypt_value($row['public_token_enc'] ?? '');
    $row['ip_address'] = anaf_decrypt_value($row['ip_address_enc'] ?? '');

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

function render_anaf_consent_form(array $site, ?array $record = null, array $errors = [], array $old = [], bool $sent = false): string
{
    if (!headers_sent()) {
        header('Cache-Control: no-store, private');
    }

    $token = (string) ($old['public_token'] ?? ($record['public_token'] ?? ''));
    $values = array_merge(anaf_empty_data(), $record ? array_intersect_key($record, anaf_empty_data()) : [], $old);

    if ($sent) {
        $body = '<section class="page-hero consent-hero">
    <div>
      <p class="eyebrow">Acord ANAF</p>
      <h1>Acordul a fost înregistrat</h1>
      <p>Îți mulțumim. Confirmarea a fost salvată în siguranță și va fi verificată de echipa Local Capital.</p>
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
        ][$field] ?? 'off';

        if ($type === 'textarea') {
            $fields .= '<label>' . e($meta['label']) . '<textarea name="' . e($field) . '" rows="4" maxlength="' . e((string) $meta['max']) . '"' . $required . ' autocomplete="' . e($autocomplete) . '">' . e($values[$field] ?? '') . '</textarea></label>';
        } else {
            $inputType = $field === 'id_issued_at' ? 'date' : 'text';
            $fields .= '<label>' . e($meta['label']) . '<input name="' . e($field) . '" type="' . e($inputType) . '" value="' . e($values[$field] ?? '') . '" maxlength="' . e((string) $meta['max']) . '"' . $required . ' autocomplete="' . e($autocomplete) . '"></label>';
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
    <form class="admin-form consent-form" action="' . e(anaf_form_action($token)) . '" method="post"' . recaptcha_form_attributes('anaf_consent') . '>
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
      <label class="checkbox privacy-check"><input name="anaf_consent" type="checkbox" value="1" ' . (!empty($old['anaf_consent']) ? 'checked' : '') . ' required> Confirm acordul pentru consultarea și prelucrarea datelor prin ANAF.</label>
      <p class="privacy">Datele sunt transmise securizat și sunt folosite doar pentru scopurile menționate. Nu trimite acest link altei persoane.</p>
      <button class="button" type="submit">Trimite acordul</button>
    </form>
    <aside class="security-note">
      <h2>Siguranța datelor</h2>
      <p>Pagina nu este indexată de motoarele de căutare. Datele sensibile sunt stocate criptat, iar trimiterea formularului este protejată cu reCAPTCHA și limitare de trafic.</p>
      <p>Poți solicita informații despre prelucrarea datelor la protectiadatelor@localcapital.ro.</p>
    </aside>
  </section>';

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

    $old = $data + ['public_token' => $token, 'anaf_consent' => $consent];

    if ($errors) {
        anaf_record_attempt($tokenHash, false);
        return ['ok' => false, 'errors' => $errors, 'old' => $old];
    }

    $encrypted = [];
    foreach (array_keys(anaf_fields()) as $field) {
        $encrypted[$field . '_enc'] = anaf_encrypt_value($data[$field]);
    }

    if ($record) {
        $stmt = db()->prepare('UPDATE anaf_consents SET status = ?, public_token_hash = NULL, public_token_enc = NULL, token_expires_at = NULL, request_reference_enc = ?, first_name_enc = ?, last_name_enc = ?, cnp_enc = ?, id_series_enc = ?, id_number_enc = ?, id_issued_by_enc = ?, id_issued_at_enc = ?, email_enc = ?, phone_enc = ?, address_enc = ?, consent_anaf = 1, consent_text_version = ?, consent_text = ?, ip_address_enc = ?, ip_hash = ?, user_agent_hash = ?, submitted_at = NOW(), updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = ?');
        $stmt->execute([
            'submitted',
            $encrypted['request_reference_enc'],
            $encrypted['first_name_enc'],
            $encrypted['last_name_enc'],
            $encrypted['cnp_enc'],
            $encrypted['id_series_enc'],
            $encrypted['id_number_enc'],
            $encrypted['id_issued_by_enc'],
            $encrypted['id_issued_at_enc'],
            $encrypted['email_enc'],
            $encrypted['phone_enc'],
            $encrypted['address_enc'],
            ANAF_CONSENT_VERSION,
            anaf_consent_text(),
            anaf_encrypt_value(client_ip()),
            client_ip_hash(),
            anaf_user_agent_hash(),
            (int) $record['id'],
            'draft',
        ]);
    } else {
        $stmt = db()->prepare('INSERT INTO anaf_consents (status, request_reference_enc, first_name_enc, last_name_enc, cnp_enc, id_series_enc, id_number_enc, id_issued_by_enc, id_issued_at_enc, email_enc, phone_enc, address_enc, consent_anaf, consent_text_version, consent_text, ip_address_enc, ip_hash, user_agent_hash, submitted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            'submitted',
            $encrypted['request_reference_enc'],
            $encrypted['first_name_enc'],
            $encrypted['last_name_enc'],
            $encrypted['cnp_enc'],
            $encrypted['id_series_enc'],
            $encrypted['id_number_enc'],
            $encrypted['id_issued_by_enc'],
            $encrypted['id_issued_at_enc'],
            $encrypted['email_enc'],
            $encrypted['phone_enc'],
            $encrypted['address_enc'],
            ANAF_CONSENT_VERSION,
            anaf_consent_text(),
            anaf_encrypt_value(client_ip()),
            client_ip_hash(),
            anaf_user_agent_hash(),
        ]);
    }

    anaf_record_attempt($tokenHash, true);
    return ['ok' => true, 'errors' => [], 'old' => []];
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

    $stmt = db()->prepare('INSERT INTO anaf_consents (status, public_token_hash, public_token_enc, token_expires_at, request_reference_enc, first_name_enc, last_name_enc, cnp_enc, id_series_enc, id_number_enc, id_issued_by_enc, id_issued_at_enc, email_enc, phone_enc, address_enc, created_by_admin_id) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        'draft',
        $tokenHash,
        anaf_encrypt_value($token),
        $encrypted['request_reference_enc'],
        $encrypted['first_name_enc'],
        $encrypted['last_name_enc'],
        $encrypted['cnp_enc'],
        $encrypted['id_series_enc'],
        $encrypted['id_number_enc'],
        $encrypted['id_issued_by_enc'],
        $encrypted['id_issued_at_enc'],
        $encrypted['email_enc'],
        $encrypted['phone_enc'],
        $encrypted['address_enc'],
        (int) $admin['id'],
    ]);

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
        'Serie CI' => $row['id_series'] ?? '',
        'Număr CI' => $row['id_number'] ?? '',
        'CI emis de' => $row['id_issued_by'] ?? '',
        'Data emiterii CI' => $row['id_issued_at'] ?? '',
        'Email' => $row['email'] ?? '',
        'Telefon' => $row['phone'] ?? '',
        'Adresă' => $row['address'] ?? '',
        'Acceptat la' => $row['submitted_at'] ?: '-',
        'Versiune acord' => $row['consent_text_version'] ?: '-',
        'IP acceptare' => $row['ip_address'] ?: '-',
        'IP hash' => $row['ip_hash'] ?: '-',
    ];
    $details = '';
    foreach ($fields as $label => $value) {
        $details .= '<div><dt>' . e($label) . '</dt><dd>' . e((string) $value) . '</dd></div>';
    }

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
    $name = trim(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? ''));
    $lines = [
        'ACORD CONSULTARE DATE PRIN ANAF',
        'LOCAL CAPITAL IFN S.A.',
        '',
        'EXEMPLU - document generat pentru verificare operationala si validare juridica.',
        '',
        'Numar cerere/dosar: ' . ($row['request_reference'] ?: '-'),
        'Client: ' . $name,
        'CNP: ' . ($row['cnp'] ?? ''),
        'Act identitate: ' . ($row['id_series'] ?? '') . ' ' . ($row['id_number'] ?? '') . ', emis de ' . ($row['id_issued_by'] ?? '') . ', la data ' . ($row['id_issued_at'] ?? ''),
        'Email: ' . ($row['email'] ?? ''),
        'Telefon: ' . ($row['phone'] ?? ''),
        'Adresa: ' . ($row['address'] ?? ''),
        '',
        'Acord acceptat la: ' . ($row['submitted_at'] ?? ''),
        'Acceptare electronica: ' . $name . ', data/ora ' . ($row['submitted_at'] ?? '') . ', IP ' . ($row['ip_address'] ?? ''),
        'Versiune text: ' . ($row['consent_text_version'] ?? ANAF_CONSENT_VERSION),
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
    $filename = 'acord-anaf-' . $id . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: no-store, private');
    echo $pdf;
}
