<?php

declare(strict_types=1);

const ROOT_DIR = __DIR__ . '/..';
const DEFAULT_LANGUAGE = 'ro';
const SUPPORTED_LANGUAGES = ['ro', 'en', 'hu'];

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        $expectedKey = 0;
        foreach ($array as $key => $_) {
            if ($key !== $expectedKey++) {
                return false;
            }
        }

        return true;
    }
}

function csp_nonce(): string
{
    static $nonce = null;

    if ($nonce === null) {
        $nonce = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
    }

    return $nonce;
}

function app_config(): array
{
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $envPath = getenv('LOCALCAPITAL_CONFIG') ?: '';
    $path = $envPath !== '' ? $envPath : ROOT_DIR . '/config/config.php';
    if (!is_file($path)) {
        $path = ROOT_DIR . '/config/config.example.php';
    }

    $config = require $path;
    return $config;
}

function asset_version(string $path): string
{
    $file = ROOT_DIR . '/public/' . ltrim($path, '/');
    if (is_file($file)) {
        return (string) filemtime($file);
    }

    return '1';
}

function is_https_request(): bool
{
    $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
    $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $forwardedSsl = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_SSL'] ?? ''));
    $frontEndHttps = strtolower((string) ($_SERVER['HTTP_FRONT_END_HTTPS'] ?? ''));
    $requestScheme = strtolower((string) ($_SERVER['REQUEST_SCHEME'] ?? ''));
    $cfVisitor = strtolower((string) ($_SERVER['HTTP_CF_VISITOR'] ?? ''));
    $serverPort = (string) ($_SERVER['SERVER_PORT'] ?? '');

    return ($https !== '' && $https !== 'off')
        || $forwardedProto === 'https'
        || $forwardedSsl === 'on'
        || $frontEndHttps === 'on'
        || $requestScheme === 'https'
        || $serverPort === '443'
        || strpos($cfVisitor, '"scheme":"https"') !== false;
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config()['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function content_update_files(): array
{
    $fileList = trim((string) (getenv('LOCALCAPITAL_CONTENT_UPDATE_FILES') ?: ''));
    if ($fileList !== '') {
        return array_map(static fn (string $file): string => ROOT_DIR . '/' . ltrim(trim($file), '/'), explode(',', $fileList));
    }

    $configured = app_config()['app']['content_update_files'] ?? null;
    if (is_array($configured) && $configured) {
        return array_map(static fn (string $file): string => ROOT_DIR . '/' . ltrim(trim($file), '/'), $configured);
    }

    return [
        ROOT_DIR . '/database/content-overrides.sql',
        ROOT_DIR . '/database/ifn-trust-content.sql',
        ROOT_DIR . '/database/multilingual-content-fixes.sql',
        ROOT_DIR . '/database/anaf-consent.sql',
    ];
}

function content_updates_enabled(): bool
{
    $env = getenv('LOCALCAPITAL_AUTO_APPLY_CONTENT');
    if ($env !== false && $env !== '') {
        return filter_var($env, FILTER_VALIDATE_BOOLEAN);
    }

    return (bool) (app_config()['app']['auto_apply_content_updates'] ?? true);
}

function content_update_force_enabled(): bool
{
    $env = getenv('LOCALCAPITAL_FORCE_CONTENT_UPDATES');
    if ($env !== false && $env !== '') {
        return filter_var($env, FILTER_VALIDATE_BOOLEAN);
    }

    return (bool) (app_config()['app']['force_content_updates'] ?? false);
}

function content_update_lock_handle()
{
    $lock = @fopen(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . '/localcapital-content-updates-' . md5(ROOT_DIR) . '.lock', 'c');
    if ($lock === false) {
        return null;
    }

    if (!flock($lock, LOCK_EX | LOCK_NB)) {
        fclose($lock);
        return null;
    }

    return $lock;
}

function release_content_update_lock($lock): void
{
    if (is_resource($lock)) {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function content_update_db(int $retries = 1, int $sleepSeconds = 1): PDO
{
    $lastError = null;
    for ($attempt = 1; $attempt <= $retries; $attempt++) {
        try {
            $pdo = db();
            $pdo->query('SELECT 1');
            return $pdo;
        } catch (Throwable $error) {
            $lastError = $error;
            if ($attempt < $retries) {
                sleep($sleepSeconds);
            }
        }
    }

    throw $lastError ?? new RuntimeException('Database connection failed.');
}

function ensure_content_update_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS content_update_runs (
            update_key VARCHAR(190) NOT NULL,
            checksum CHAR(64) NOT NULL,
            applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (update_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function content_update_statuses(): array
{
    $pdo = content_update_db();
    ensure_content_update_table($pdo);

    $runs = [];
    foreach ($pdo->query('SELECT update_key, checksum, applied_at FROM content_update_runs') as $row) {
        $runs[(string) $row['update_key']] = [
            'checksum' => (string) $row['checksum'],
            'applied_at' => (string) $row['applied_at'],
        ];
    }

    $statuses = [];
    $applyRemaining = false;
    foreach (content_update_files() as $file) {
        $key = str_replace(ROOT_DIR . '/', '', $file);
        $run = $runs[$key] ?? null;
        $exists = is_file($file);
        $checksum = $exists ? hash_file('sha256', $file) : null;
        $status = 'missing';

        if ($exists && (!$run || ($run['checksum'] ?? '') !== $checksum)) {
            $status = 'pending';
            $applyRemaining = true;
        } elseif ($exists && $applyRemaining) {
            $status = 'queued';
        } elseif ($exists) {
            $status = 'ran';
        }

        $statuses[] = [
            'key' => $key,
            'file' => basename($file),
            'path' => $file,
            'exists' => $exists,
            'status' => $status,
            'checksum' => $checksum,
            'previous_checksum' => $run['checksum'] ?? null,
            'applied_at' => $run['applied_at'] ?? null,
        ];
    }

    return $statuses;
}

function apply_content_updates(bool $dryRun = false, bool $force = false, int $retries = 1, int $sleepSeconds = 1): array
{
    $pdo = $dryRun ? null : content_update_db($retries, $sleepSeconds);
    if ($pdo instanceof PDO) {
        ensure_content_update_table($pdo);
    }

    $results = [];
    $pendingChecksumUpdates = [];
    $applyRemaining = $force;
    foreach (content_update_files() as $file) {
        if (!is_file($file)) {
            $results[] = ['file' => basename($file), 'status' => 'missing'];
            continue;
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException($file . ' could not be read.');
        }

        $key = str_replace(ROOT_DIR . '/', '', $file);
        $checksum = hash('sha256', $sql);

        if ($dryRun) {
            $results[] = ['file' => $key, 'status' => 'ready', 'checksum' => $checksum];
            continue;
        }

        $stmt = $pdo->prepare('SELECT checksum FROM content_update_runs WHERE update_key = ?');
        $stmt->execute([$key]);
        $previousChecksum = $stmt->fetchColumn();

        if (!$applyRemaining && $previousChecksum === $checksum) {
            $results[] = ['file' => $key, 'status' => 'unchanged', 'checksum' => $checksum];
            continue;
        }

        $pdo->exec($sql);
        $pendingChecksumUpdates[] = [$key, $checksum];
        $results[] = ['file' => $key, 'status' => 'applied', 'checksum' => $checksum];
        $applyRemaining = true;
    }

    if ($pdo instanceof PDO && $pendingChecksumUpdates) {
        $stmt = $pdo->prepare('INSERT INTO content_update_runs (update_key, checksum) VALUES (?, ?) ON DUPLICATE KEY UPDATE checksum = VALUES(checksum), applied_at = CURRENT_TIMESTAMP');
        foreach ($pendingChecksumUpdates as [$key, $checksum]) {
            $stmt->execute([$key, $checksum]);
        }
    }

    return $results;
}

function apply_content_updates_with_lock(bool $force = false, int $retries = 1, int $sleepSeconds = 1): array
{
    $lock = content_update_lock_handle();
    if ($lock === null) {
        throw new RuntimeException('Content updates are already running.');
    }

    try {
        return apply_content_updates(false, $force, $retries, $sleepSeconds);
    } finally {
        release_content_update_lock($lock);
    }
}

function apply_content_updates_for_request(): void
{
    static $done = false;
    if ($done || !content_updates_enabled()) {
        return;
    }
    $done = true;

    $lock = content_update_lock_handle();
    if ($lock === null) {
        return;
    }

    try {
        apply_content_updates(false, content_update_force_enabled());
    } catch (Throwable $error) {
        error_log('LOCALCAPITAL_CONTENT_UPDATE_FAILED ' . $error->getMessage());
    } finally {
        release_content_update_lock($lock);
    }
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = app_config()['app'];
    $secure = is_https_request();

    session_name($config['session_name'] ?? 'LC_ADMIN');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/admin',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }

    $nonce = csp_nonce();
    $secure = is_https_request();
    $recaptchaEnabled = request_needs_recaptcha_assets();
    $csp = [
        "default-src 'self'",
        "script-src 'unsafe-inline' 'nonce-" . $nonce . "'" . ($recaptchaEnabled ? " https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/" : ''),
        "style-src 'self'" . ($recaptchaEnabled ? " 'unsafe-inline'" : ''),
        "img-src 'self' data:",
        "font-src 'self'",
        "connect-src 'self'" . ($recaptchaEnabled ? " https://www.google.com/recaptcha/" : ''),
        "frame-src " . ($recaptchaEnabled ? "'self' https://www.google.com/recaptcha/ https://recaptcha.google.com/recaptcha/" : "'none'"),
        "manifest-src 'self'",
        "object-src 'none'",
        "form-action 'self'",
        "base-uri 'none'",
        "frame-ancestors 'none'",
    ];
    if (!$recaptchaEnabled) {
        $csp[] = "require-trusted-types-for 'script'";
        $csp[] = "trusted-types default";
    }
    if ($secure) {
        $csp[] = 'upgrade-insecure-requests';
    }

    $origin = app_origin_url();

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: accelerometer=(), autoplay=(), camera=(), display-capture=(), encrypted-media=(), fullscreen=(self), geolocation=(), gyroscope=(), microphone=(), midi=(), payment=(), usb=(), interest-cohort=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    header('Origin-Agent-Cluster: ?1');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin', false);
    header('X-Robots-Tag: noai, noimageai');
    header('Content-Security-Policy: ' . implode('; ', $csp));

    if ($secure) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

function request_needs_recaptcha_assets(): bool
{
    if (!recaptcha_enabled()) {
        return false;
    }

    $requestPath = normalize_route_path(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    if ($requestPath === '/admin/login') {
        return true;
    }

    [$language, $path] = detect_language_and_path();
    if (route_page_key_for_path($language, $path) === 'contact') {
        return true;
    }

    return $path === '/acord-anaf' || str_starts_with($path . '/', '/acord-anaf/');
}

function app_origin_url(): string
{
    $baseUrl = app_base_url();
    $parts = parse_url($baseUrl);
    $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
    $host = strtolower((string) ($parts['host'] ?? 'localhost'));
    $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

    return $scheme . '://' . $host . $port;
}

function request_looks_malicious(string $value): bool
{
    $decoded = strtolower(rawurldecode($value));
    $patterns = [
        '/<\s*script\b/',
        '/javascript\s*:/',
        '/onerror\s*=/',
        '/\.\.\//',
        '/\.\.\\\\/',
        '#/etc/passwd#',
        '#php://#',
        '/\bunion\s+select\b/',
        '/\binformation_schema\b/',
        '/\bbenchmark\s*\(/',
        '/\bsleep\s*\(/',
        '/\bwaitfor\s+delay\b/',
        '/\bxp_cmdshell\b/',
        '/\$\{\s*jndi\s*:/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $decoded)) {
            return true;
        }
    }

    return false;
}

function enforce_request_hardening(): void
{
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if (!in_array($method, ['GET', 'HEAD', 'POST'], true)) {
        header('Allow: GET, POST, HEAD');
        http_response_code(405);
        exit;
    }

    $path = (string) ($_SERVER['REQUEST_URI'] ?? '/');
    $requestPath = parse_url($path, PHP_URL_PATH) ?: '/';
    if (request_user_agent_is_blocked((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), $requestPath)) {
        http_response_code(403);
        exit;
    }

    $query = (string) ($_SERVER['QUERY_STRING'] ?? '');
    if (request_looks_malicious($path . "\n" . $query)) {
        http_response_code(403);
        exit;
    }
}

function blocked_scraper_user_agents(): array
{
    return [
        'amazonbot',
        'anthropic-ai',
        'applebot-extended',
        'bytespider',
        'ccbot',
        'chatgpt-user',
        'claude-searchbot',
        'claude-user',
        'claudebot',
        'deepseekbot',
        'facebookbot',
        'google-extended',
        'gptbot',
        'meta-externalagent',
        'meta-externalfetcher',
        'omgili',
        'perplexity-user',
        'perplexitybot',
    ];
}

function request_user_agent_is_blocked(string $userAgent, string $path): bool
{
    $path = normalize_route_path($path);
    if (in_array($path, ['/robots.txt', '/llms.txt', '/sitemap.xml'], true)) {
        return false;
    }

    $userAgent = strtolower($userAgent);
    foreach (blocked_scraper_user_agents() as $blocked) {
        if ($blocked !== '' && str_contains($userAgent, $blocked)) {
            return true;
        }
    }

    return false;
}

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function repair_text_encoding(string $value): string
{
    static $replacements = null;

    if ($replacements === null) {
        $characters = 'ăĂâÂîÎșȘşŞțȚţŢáÁéÉíÍóÓöÖőŐúÚüÜűŰ–—‘’“”…• ';
        $replacements = [];
        foreach (preg_split('//u', $characters, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $character) {
            $broken = @iconv('Windows-1252', 'UTF-8//IGNORE', $character);
            if (is_string($broken) && $broken !== '' && $broken !== $character) {
                $replacements[$broken] = $character;
            }
        }
        $replacements += [
            'Â ' => ' ',
        ];
    }

    return strtr($value, $replacements);
}

function repair_data_encoding($value)
{
    if (is_string($value)) {
        return repair_text_encoding($value);
    }

    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = repair_data_encoding($item);
        }
    }

    return $value;
}

function slugify(string $value): string
{
    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $normalized = strtolower($normalized ?: $value);
    $normalized = preg_replace('/[^a-z0-9]+/', '-', $normalized) ?? '';
    $normalized = trim($normalized, '-');
    return substr($normalized, 0, 72);
}

function plain_text(string $value, int $length = 160): string
{
    $stripped = preg_replace('/[#*_`>-]/', ' ', $value) ?? $value;
    $text = trim((string) (preg_replace('/\s+/', ' ', $stripped) ?? $stripped));
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }

        return trim(mb_substr($text, 0, max(0, $length - 3), 'UTF-8')) . '...';
    }

    if (strlen($text) <= $length) {
        return $text;
    }

    return trim(substr($text, 0, max(0, $length - 3))) . '...';
}

function app_base_url(): string
{
    $configured = trim((string) (app_config()['app']['base_url'] ?? ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return ($secure ? 'https://' : 'http://') . $host;
}

function absolute_url(string $path = '/'): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }

    return app_base_url() . '/' . ltrim($path ?: '/', '/');
}

function seo_description(string $value): string
{
    return plain_text($value, 158);
}

function locale_for_language(string $language): string
{
    return [
        'ro' => 'ro_RO',
        'en' => 'en_US',
        'hu' => 'hu_HU',
    ][$language] ?? 'ro_RO';
}

function form_secret(): string
{
    $config = app_config();
    $secret = (string) ($config['app']['form_secret'] ?? '');
    if ($secret !== '') {
        return $secret;
    }

    return hash('sha256', serialize($config['db']) . ROOT_DIR);
}

function contact_form_token(?int $timestamp = null): string
{
    $timestamp ??= time();
    $signature = hash_hmac('sha256', (string) $timestamp, form_secret());
    return $timestamp . '.' . $signature;
}

function verify_contact_form_token(string $token): bool
{
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        return false;
    }

    $timestamp = (int) $parts[0];
    if ($timestamp < time() - 7200 || $timestamp > time() + 300) {
        return false;
    }

    return hash_equals(contact_form_token($timestamp), $token);
}

function client_ip_hash(): string
{
    return hash_hmac('sha256', client_ip(), form_secret());
}

function client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

function security_policy(string $key, int $default): int
{
    $config = app_config()['security'] ?? [];
    $value = (int) ($config[$key] ?? $default);
    return max(1, $value);
}

function recaptcha_config(): array
{
    $config = app_config()['recaptcha'] ?? [];
    return is_array($config) ? $config : [];
}

function recaptcha_enabled(): bool
{
    $config = recaptcha_config();
    return !empty($config['enabled'])
        && trim((string) ($config['site_key'] ?? '')) !== ''
        && trim((string) ($config['secret_key'] ?? '')) !== '';
}

function recaptcha_site_key(): string
{
    return (string) (recaptcha_config()['site_key'] ?? '');
}

function recaptcha_action_score(string $action): float
{
    $config = recaptcha_config();
    $actions = is_array($config['actions'] ?? null) ? $config['actions'] : [];
    return (float) ($actions[$action] ?? ($config['min_score'] ?? 0.5));
}

function recaptcha_verify(string $action, ?string $token = null): array
{
    if (!recaptcha_enabled()) {
        return ['ok' => true, 'score' => 1.0, 'disabled' => true];
    }

    $token = trim((string) ($token ?? ($_POST['recaptcha_token'] ?? '')));
    if ($token === '') {
        return ['ok' => false, 'reason' => 'missing-token', 'score' => 0.0];
    }

    $config = recaptcha_config();
    $body = http_build_query([
        'secret' => (string) ($config['secret_key'] ?? ''),
        'response' => $token,
        'remoteip' => client_ip(),
    ]);
    $response = recaptcha_post_verify($body);
    if (!is_array($response)) {
        return ['ok' => false, 'reason' => 'verify-unavailable', 'score' => 0.0];
    }

    $score = (float) ($response['score'] ?? 0.0);
    $expectedScore = recaptcha_action_score($action);
    $verifiedAction = (string) ($response['action'] ?? '');
    $success = !empty($response['success']);

    if (!$success || $verifiedAction !== $action || $score < $expectedScore) {
        return [
            'ok' => false,
            'reason' => !$success ? 'failed' : ($verifiedAction !== $action ? 'action-mismatch' : 'low-score'),
            'score' => $score,
            'action' => $verifiedAction,
        ];
    }

    return ['ok' => true, 'score' => $score, 'action' => $verifiedAction];
}

function recaptcha_post_verify(string $body): ?array
{
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $raw = null;

    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        if ($curl) {
            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            ]);
            $raw = curl_exec($curl);
            curl_close($curl);
        }
    }

    if ($raw === null || $raw === false) {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => 5,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
    }

    if (!is_string($raw) || $raw === '') {
        return null;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function recaptcha_error_text(string $language): string
{
    return [
        'ro' => 'Verificarea reCAPTCHA nu a reușit. Reîncarcă pagina și încearcă din nou.',
        'en' => 'The reCAPTCHA verification failed. Reload the page and try again.',
        'hu' => 'A reCAPTCHA ellenőrzés nem sikerült. Töltsd újra az oldalt és próbáld meg újra.',
    ][normalize_language($language)] ?? 'The reCAPTCHA verification failed.';
}

function clean_form_text(string $value, int $limit): string
{
    $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $limit, 'UTF-8');
    }

    return substr($value, 0, $limit);
}

function contact_message_rate_limited(string $ipHash): bool
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM contact_messages WHERE ip_hash = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)');
    $stmt->execute([$ipHash]);
    return (int) $stmt->fetchColumn() >= 5;
}

function contact_error_text(string $language, string $key): string
{
    static $messages = [
        'ro' => [
            'expired' => 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.',
            'spam' => 'Mesajul nu a putut fi trimis.',
            'name' => 'Completează numele.',
            'email' => 'Completează o adresă de email validă.',
            'subject' => 'Completează subiectul.',
            'message' => 'Completează mesajul.',
            'privacy' => 'Confirmă că ai citit informarea privind prelucrarea datelor personale.',
            'rate' => 'Ai trimis prea multe mesaje într-un timp scurt. Te rugăm să revii peste câteva minute.',
        ],
        'en' => [
            'expired' => 'The form has expired. Reload the page and try again.',
            'spam' => 'The message could not be sent.',
            'name' => 'Enter your name.',
            'email' => 'Enter a valid email address.',
            'subject' => 'Enter a subject.',
            'message' => 'Enter your message.',
            'privacy' => 'Confirm that you have read the personal data processing notice.',
            'rate' => 'You sent too many messages in a short time. Please try again in a few minutes.',
        ],
        'hu' => [
            'expired' => 'Az űrlap lejárt. Töltsd újra az oldalt, és próbáld meg újra.',
            'spam' => 'Az üzenetet nem sikerült elküldeni.',
            'name' => 'Add meg a neved.',
            'email' => 'Adj meg egy érvényes e-mail címet.',
            'subject' => 'Add meg a tárgyat.',
            'message' => 'Írd be az üzenetet.',
            'privacy' => 'Erősítsd meg, hogy elolvastad a személyes adatok kezeléséről szóló tájékoztatót.',
            'rate' => 'Rövid idő alatt túl sok üzenetet küldtél. Kérjük, próbáld újra néhány perc múlva.',
        ],
    ];

    $language = normalize_language($language);
    return $messages[$language][$key] ?? $messages[DEFAULT_LANGUAGE][$key] ?? $key;
}

function save_contact_message(string $language, array $input): array
{
    $errors = [];

    if (!verify_contact_form_token((string) ($input['contact_token'] ?? ''))) {
        $errors[] = contact_error_text($language, 'expired');
    }

    if (trim((string) ($input['website'] ?? '')) !== '') {
        $errors[] = contact_error_text($language, 'spam');
    }

    $recaptcha = recaptcha_verify('contact', (string) ($input['recaptcha_token'] ?? ''));
    if (empty($recaptcha['ok'])) {
        $errors[] = recaptcha_error_text($language);
    }

    $name = clean_form_text((string) ($input['name'] ?? ''), 160);
    $email = clean_form_text((string) ($input['email'] ?? ''), 190);
    $phone = clean_form_text((string) ($input['phone'] ?? ''), 60);
    $subject = clean_form_text((string) ($input['subject'] ?? ''), 220);
    $message = trim((string) ($input['message'] ?? ''));
    if (function_exists('mb_substr')) {
        $message = mb_substr($message, 0, 4000, 'UTF-8');
    } else {
        $message = substr($message, 0, 4000);
    }
    $consent = isset($input['privacy']);

    if ($name === '') {
        $errors[] = contact_error_text($language, 'name');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = contact_error_text($language, 'email');
    }
    if ($subject === '') {
        $errors[] = contact_error_text($language, 'subject');
    }
    if (trim($message) === '') {
        $errors[] = contact_error_text($language, 'message');
    }
    if (!$consent) {
        $errors[] = contact_error_text($language, 'privacy');
    }

    $ipHash = client_ip_hash();
    if (!$errors && contact_message_rate_limited($ipHash)) {
        $errors[] = contact_error_text($language, 'rate');
    }

    $old = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => $message,
        'privacy' => $consent,
    ];

    if ($errors) {
        return ['ok' => false, 'errors' => $errors, 'old' => $old];
    }

    $stmt = db()->prepare('INSERT INTO contact_messages (language_code, name, email, phone, subject, message, status, ip_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        normalize_language($language),
        $name,
        $email,
        $phone !== '' ? $phone : null,
        $subject,
        $message,
        'new',
        $ipHash,
    ]);

    return ['ok' => true, 'errors' => [], 'old' => []];
}

function render_inline(string $value): string
{
    return preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', e($value)) ?? e($value);
}

function render_markdown(?string $markdown): string
{
    $lines = preg_split('/\r\n|\r|\n/', (string) $markdown) ?: [];
    $output = [];
    $paragraph = [];
    $list = [];

    $flushParagraph = function () use (&$paragraph, &$output): void {
        if (!$paragraph) {
            return;
        }
        $output[] = '<p>' . render_inline(implode(' ', $paragraph)) . '</p>';
        $paragraph = [];
    };

    $flushList = function () use (&$list, &$output): void {
        if (!$list) {
            return;
        }
        $items = array_map(fn ($item) => '<li>' . render_inline($item) . '</li>', $list);
        $output[] = '<ul>' . implode('', $items) . '</ul>';
        $list = [];
    };

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            $flushParagraph();
            $flushList();
            continue;
        }

        if (preg_match('/^(#{1,3})\s+(.+)$/', $trimmed, $match)) {
            $flushParagraph();
            $flushList();
            $level = min(strlen($match[1]) + 1, 4);
            $output[] = '<h' . $level . '>' . render_inline($match[2]) . '</h' . $level . '>';
            continue;
        }

        if (preg_match('/^[-*]\s+(.+)$/', $trimmed, $match)) {
            $flushParagraph();
            $list[] = $match[1];
            continue;
        }

        $flushList();
        $paragraph[] = $trimmed;
    }

    $flushParagraph();
    $flushList();

    return implode("\n", $output);
}

function normalize_language(?string $language): string
{
    $language = strtolower((string) $language);
    return in_array($language, SUPPORTED_LANGUAGES, true) ? $language : DEFAULT_LANGUAGE;
}

function language_prefix(string $language): string
{
    return $language === DEFAULT_LANGUAGE ? '' : '/' . $language;
}

function normalize_route_path(string $path): string
{
    $path = '/' . ltrim($path ?: '/', '/');
    $path = $path === '//' ? '/' : $path;
    return $path === '/' ? '/' : rtrim($path, '/');
}

function localized_page_routes(): array
{
    return [
        'ro' => [
            'home' => '/',
            'about' => '/despre-noi',
            'contract' => '/contract',
            'guide' => '/ghid-client',
            'contact' => '/contact',
            'accessibility' => '/accesibilitate',
            'gdpr' => '/gdpr',
            'privacy' => '/politica-privind-datele-personale',
            'terms' => '/termene-si-conditii',
        ],
        'en' => [
            'home' => '/',
            'about' => '/about-us',
            'contract' => '/loan-types',
            'guide' => '/client-guide',
            'contact' => '/contact',
            'accessibility' => '/accessibility',
            'gdpr' => '/gdpr',
            'privacy' => '/personal-data-policy',
            'terms' => '/terms-and-conditions',
        ],
        'hu' => [
            'home' => '/',
            'about' => '/rolunk',
            'contract' => '/hitelek',
            'guide' => '/ugyfel-tajekoztato',
            'contact' => '/kapcsolat',
            'accessibility' => '/akadalymentesites',
            'gdpr' => '/gdpr',
            'privacy' => '/szemelyes-adatok-kezelese',
            'terms' => '/altalanos-szerzodesi-feltetelek',
        ],
    ];
}

function localized_page_aliases(): array
{
    return [
        'ro' => [
            'privacy' => [
                '/privacy',
                '/privacy-policy',
                '/politica-de-confidentialitate',
                '/politica-confidentialitate',
                '/protectia-datelor',
                '/cookies',
                '/cookie-policy',
            ],
        ],
        'en' => [
            'privacy' => [
                '/privacy',
                '/privacy-policy',
                '/personal-data-policy',
                '/data-protection',
                '/cookies',
                '/cookie-policy',
            ],
        ],
        'hu' => [
            'privacy' => [
                '/privacy',
                '/privacy-policy',
                '/adatvedelem',
                '/adatkezeles',
                '/adatkezelesi-tajekoztato',
                '/cookies',
                '/cookie-policy',
            ],
        ],
    ];
}

function blog_index_routes(): array
{
    return [
        'ro' => '/blog',
        'en' => '/news',
        'hu' => '/hirek',
    ];
}

function case_study_archive_routes(): array
{
    return [
        'ro' => '/finlon-case-study-category/business',
        'en' => '/case-studies/business',
        'hu' => '/esettanulmanyok/uzlet',
    ];
}

function post_type_prefixes(): array
{
    return [
        'post' => [
            'ro' => '/blog',
            'en' => '/articles',
            'hu' => '/cikkek',
        ],
        'service' => [
            'ro' => '/service',
            'en' => '/services',
            'hu' => '/szolgaltatasok',
        ],
        'case_study' => [
            'ro' => '/case_study',
            'en' => '/case-studies',
            'hu' => '/esettanulmanyok',
        ],
    ];
}

function post_slug_routes(): array
{
    return [
        'post' => [
            'credit-rapid-cu-buletinul' => [
                'ro' => 'credit-rapid-cu-buletinul',
                'en' => 'fast-credit-with-identity-card',
                'hu' => 'gyors-hitel-szemelyi-igazolvannyal',
            ],
        ],
        'service' => [
            'auto-car-loan' => [
                'ro' => 'credit-pentru-masina-noua',
                'en' => 'new-car-loan',
                'hu' => 'uj-auto-hitel',
            ],
            'business-loan' => [
                'ro' => 'credit-pentru-proiecte-si-investitii',
                'en' => 'projects-and-investments-loan',
                'hu' => 'projektek-es-befektetesek-hitele',
            ],
            'education-loan' => [
                'ro' => 'credit-pentru-educatie',
                'en' => 'education-loan',
                'hu' => 'oktatasi-hitel',
            ],
            'personal-loan' => [
                'ro' => 'credit-pentru-nevoi-personale',
                'en' => 'personal-loan',
                'hu' => 'szemelyi-kolcson',
            ],
            'property-loan' => [
                'ro' => 'credit-pentru-locuinta',
                'en' => 'home-improvement-loan',
                'hu' => 'lakasfelujitasi-hitel',
            ],
            'wedding-loan' => [
                'ro' => 'credit-pentru-evenimente-de-familie',
                'en' => 'family-events-loan',
                'hu' => 'csaladi-esemenyek-hitele',
            ],
        ],
        'case_study' => [
            'business-planning' => [
                'ro' => 'planificare-business',
                'en' => 'business-planning',
                'hu' => 'uzleti-tervezes',
            ],
            'business-tie-ups' => [
                'ro' => 'parteneriate-business',
                'en' => 'business-partnerships',
                'hu' => 'uzleti-egyuttmukodesek',
            ],
            'meger-acquistion' => [
                'ro' => 'fuziuni-si-achizitii',
                'en' => 'mergers-and-acquisitions',
                'hu' => 'fuzio-es-felvasarlas',
            ],
            'personal-banking' => [
                'ro' => 'servicii-financiare-personale',
                'en' => 'personal-banking',
                'hu' => 'szemelyes-penzugyek',
            ],
        ],
    ];
}

function route_page_key_for_path(string $language, string $path): ?string
{
    $language = normalize_language($language);
    $path = normalize_route_path($path);
    $routes = localized_page_routes();
    $aliases = localized_page_aliases();

    foreach ([$routes[$language] ?? [], $routes[DEFAULT_LANGUAGE]] as $allowedRoutes) {
        foreach ($allowedRoutes as $key => $routePath) {
            if (normalize_route_path($routePath) === $path) {
                return $key;
            }
        }
    }

    foreach ([$aliases[$language] ?? [], $aliases[DEFAULT_LANGUAGE] ?? []] as $allowedAliases) {
        foreach ($allowedAliases as $key => $routePaths) {
            foreach ((array) $routePaths as $routePath) {
                if (normalize_route_path($routePath) === $path) {
                    return $key;
                }
            }
        }
    }

    return null;
}

function route_any_page_key_for_path(string $path): ?string
{
    $path = normalize_route_path($path);
    foreach (localized_page_routes() as $routes) {
        foreach ($routes as $key => $routePath) {
            if (normalize_route_path($routePath) === $path) {
                return $key;
            }
        }
    }

    return null;
}

function route_page_path(string $key, string $language): ?string
{
    $language = normalize_language($language);
    $routes = localized_page_routes();
    return $routes[$language][$key] ?? $routes[DEFAULT_LANGUAGE][$key] ?? null;
}

function legal_page_fallbacks(string $language): array
{
    $language = normalize_language($language);

    $content = [
        'ro' => [
            'gdpr' => [
                'title' => 'GDPR',
                'summary' => 'Informații despre prelucrarea datelor personale de către LOCAL CAPITAL IFN S.A.',
                'body' => '## Protecția datelor personale

LOCAL CAPITAL IFN S.A. prelucrează date personale în conformitate cu Regulamentul (UE) 2016/679 (GDPR), legislația română aplicabilă și documentele interne privind securitatea datelor.

## Operatorul datelor

Operatorul este LOCAL CAPITAL IFN S.A. Pentru întrebări privind protecția datelor, poți scrie la info@localcapital.ro sau protectiadatelor@localcapital.ro.

## Drepturile persoanei vizate

Ai dreptul de acces, rectificare, ștergere, restricționare, opoziție, portabilitate și dreptul de a depune o plângere la autoritatea competentă.

## Securitate

Folosim măsuri tehnice și organizatorice pentru limitarea accesului neautorizat, protejarea formularului de contact și reducerea riscurilor de securitate.',
            ],
            'privacy' => [
                'title' => 'Politica de confidențialitate',
                'summary' => 'Informații despre datele personale, cookie-uri, formulare și canalele de contact Local Capital.',
                'body' => '## Politica de confidențialitate

Această pagină explică modul în care LOCAL CAPITAL IFN S.A. gestionează datele personale transmise prin website, inclusiv datele introduse în formularul de contact.

## Date colectate

Putem prelucra nume, telefon, email, subiectul solicitării, mesajul transmis, consimțământul privind informarea și date tehnice necesare funcționării website-ului.

## Scopuri

Datele sunt folosite pentru a răspunde solicitărilor, pentru comunicare, pentru protecția website-ului și pentru îndeplinirea obligațiilor legale.

## Cookie-uri

Website-ul folosește cookie-uri necesare pentru funcționare și preferințe. Cookie-urile opționale sunt folosite doar după consimțământ, dacă vor fi activate.

## Contact

Pentru întrebări privind datele personale, contactează info@localcapital.ro sau protectiadatelor@localcapital.ro.',
            ],
            'terms' => [
                'title' => 'Termene și condiții',
                'summary' => 'Reguli generale privind folosirea website-ului Local Capital.',
                'body' => '## Termene și condiții

Informațiile publicate pe website sunt informative și nu reprezintă aprobare garantată, ofertă personalizată sau consultanță financiară.

## Folosirea website-ului

Utilizatorii trebuie să folosească website-ul în mod legal și să nu transmită conținut abuziv, automatizat sau care poate afecta securitatea serviciului.

## Informații despre credit

Orice decizie de creditare se bazează pe analiza solicitării, eligibilitate și documentele comunicate înainte de semnarea contractului.',
            ],
            'accessibility' => [
                'title' => 'Declarație de accesibilitate',
                'summary' => 'Informații despre măsurile de accesibilitate ale website-ului Local Capital.',
                'body' => '## Angajamentul nostru

LOCAL CAPITAL IFN S.A. urmărește ca website-ul Local Capital să poată fi folosit de cât mai multe persoane, inclusiv de persoane care folosesc tastatura, cititoare de ecran, mărire de text sau setări de contrast ridicat.

## Funcții disponibile

- navigare cu tastatura și link „sari la conținut”;
- indicator vizibil de focus pentru elementele interactive;
- opțiuni pentru text mai mare, contrast ridicat, linkuri subliniate și reducerea animațiilor.

## Feedback

Dacă întâmpini o barieră de accesibilitate, scrie la info@localcapital.ro sau protectiadatelor@localcapital.ro.',
            ],
        ],
        'en' => [
            'gdpr' => [
                'title' => 'GDPR',
                'summary' => 'Information about personal data processing by LOCAL CAPITAL IFN S.A.',
                'body' => '## Personal data protection

LOCAL CAPITAL IFN S.A. processes personal data in accordance with Regulation (EU) 2016/679 (GDPR), applicable Romanian law, and internal data security rules.

## Data controller

The controller is LOCAL CAPITAL IFN S.A. For data protection questions, contact info@localcapital.ro or protectiadatelor@localcapital.ro.

## Data subject rights

You may request access, rectification, erasure, restriction, objection, portability, and you may lodge a complaint with the competent authority.',
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'summary' => 'Information about personal data, cookies, forms, and Local Capital contact channels.',
                'body' => '## Privacy Policy

This page explains how LOCAL CAPITAL IFN S.A. handles personal data sent through the website, including contact form data.

## Data collected

We may process name, phone, email, request subject, message content, consent confirmation, and technical data needed for website operation and security.

## Purposes

Data is used to answer requests, communicate with users, protect the website, and meet legal obligations.

## Cookies

The website uses necessary cookies for operation and preferences. Optional cookies are used only after consent, if such features are enabled.',
            ],
            'terms' => [
                'title' => 'Terms and conditions',
                'summary' => 'General rules for using the Local Capital website.',
                'body' => '## Terms and conditions

Information published on the website is informative and does not represent guaranteed approval, a personalized offer, or financial advice.

## Website use

Users must use the website lawfully and must not send abusive, automated, or security-impacting content.',
            ],
            'accessibility' => [
                'title' => 'Accessibility statement',
                'summary' => 'Information about Local Capital website accessibility measures.',
                'body' => '## Our commitment

LOCAL CAPITAL IFN S.A. aims to make the Local Capital website usable by as many people as possible, including people who use a keyboard, screen reader, text enlargement, or high-contrast settings.

## Available features

- keyboard navigation and a skip-to-content link;
- visible focus indicator for interactive elements;
- options for larger text, high contrast, underlined links, and reduced motion.

## Feedback

If you encounter an accessibility barrier, contact info@localcapital.ro or protectiadatelor@localcapital.ro.',
            ],
        ],
        'hu' => [
            'gdpr' => [
                'title' => 'GDPR',
                'summary' => 'Tájékoztatás a LOCAL CAPITAL IFN S.A. személyes adatkezeléséről.',
                'body' => '## Személyes adatok védelme

A LOCAL CAPITAL IFN S.A. a személyes adatokat az (EU) 2016/679 rendelet (GDPR), a román jogszabályok és a belső adatbiztonsági szabályok szerint kezeli.

## Adatkezelő

Az adatkezelő a LOCAL CAPITAL IFN S.A. Adatvédelmi kérdésekben az info@localcapital.ro vagy a protectiadatelor@localcapital.ro címen lehet kapcsolatba lépni.

## Érintetti jogok

Kérhető hozzáférés, helyesbítés, törlés, korlátozás, tiltakozás, adathordozhatóság, és panasz nyújtható be az illetékes hatóságnál.',
            ],
            'privacy' => [
                'title' => 'Adatvédelmi tájékoztató',
                'summary' => 'Információk a személyes adatokról, sütikről, űrlapokról és a Local Capital kapcsolati csatornáiról.',
                'body' => '## Adatvédelmi tájékoztató

Ez az oldal bemutatja, hogyan kezeli a LOCAL CAPITAL IFN S.A. a weboldalon keresztül küldött személyes adatokat, beleértve a kapcsolatfelvételi űrlap adatait.

## Kezelt adatok

Kezelhető a név, telefon, email, a kérés tárgya, az üzenet tartalma, a tájékoztatás elfogadása és a weboldal működéséhez szükséges technikai adat.

## Célok

Az adatokat megkeresések megválaszolására, kapcsolattartásra, a weboldal védelmére és jogi kötelezettségek teljesítésére használjuk.

## Sütik

A weboldal szükséges sütiket használ a működéshez és preferenciákhoz. Opcionális sütik csak hozzájárulás után használhatók.',
            ],
            'terms' => [
                'title' => 'Általános szerződési feltételek',
                'summary' => 'A Local Capital weboldal használatának általános szabályai.',
                'body' => '## Általános szerződési feltételek

A weboldalon közzétett információk tájékoztató jellegűek, és nem jelentenek garantált jóváhagyást, személyre szabott ajánlatot vagy pénzügyi tanácsadást.

## Weboldal használata

A felhasználók kötelesek a weboldalt jogszerűen használni, és nem küldhetnek visszaélésszerű, automatizált vagy biztonságot veszélyeztető tartalmat.',
            ],
            'accessibility' => [
                'title' => 'Akadálymentesítési nyilatkozat',
                'summary' => 'Tájékoztatás a Local Capital weboldal akadálymentesítési megoldásairól.',
                'body' => '## Elkötelezettségünk

A LOCAL CAPITAL IFN S.A. célja, hogy a Local Capital weboldalt minél több ember használhassa, beleértve azokat is, akik billentyűzettel, képernyőolvasóval, nagyított szöveggel vagy magas kontrasztú beállításokkal böngésznek.

## Elérhető funkciók

- billentyűzetes navigáció és „ugrás a tartalomra” link;
- jól látható fókuszjelzés az interaktív elemeknél;
- nagyobb szöveg, magas kontraszt, aláhúzott linkek és csökkentett animációk.

## Visszajelzés

Akadálymentesítési probléma esetén írj az info@localcapital.ro vagy protectiadatelor@localcapital.ro címre.',
            ],
        ],
    ];

    $fallbacks = [];
    foreach ($content[$language] ?? $content[DEFAULT_LANGUAGE] as $key => $page) {
        $fallbacks[$key] = array_merge([
            'path' => route_page_path($key, $language) ?? '/',
            'ctaLabel' => null,
            'ctaHref' => null,
            'secondaryCtaLabel' => null,
            'secondaryCtaHref' => null,
        ], $page);
    }

    return $fallbacks;
}

function blog_index_path(string $language): string
{
    $language = normalize_language($language);
    $routes = blog_index_routes();
    return $routes[$language] ?? $routes[DEFAULT_LANGUAGE];
}

function blog_index_path_matches(string $language, string $path): bool
{
    $path = normalize_route_path($path);
    return in_array($path, array_unique([blog_index_path($language), blog_index_path(DEFAULT_LANGUAGE)]), true);
}

function case_study_archive_path(string $language): string
{
    $language = normalize_language($language);
    $routes = case_study_archive_routes();
    return $routes[$language] ?? $routes[DEFAULT_LANGUAGE];
}

function case_study_archive_path_matches(string $language, string $path): bool
{
    $path = normalize_route_path($path);
    return in_array($path, array_unique([case_study_archive_path($language), case_study_archive_path(DEFAULT_LANGUAGE)]), true);
}

function canonical_slug_for_route(string $sourceType, string $slug): string
{
    $sourceType = $sourceType ?: 'post';
    $slug = trim($slug, '/');
    foreach (post_slug_routes()[$sourceType] ?? [] as $canonical => $localizedSlugs) {
        if ($slug === $canonical || in_array($slug, $localizedSlugs, true)) {
            return $canonical;
        }
    }

    return $slug;
}

function localized_slug_for_route(string $sourceType, string $slug, string $language): string
{
    $sourceType = $sourceType ?: 'post';
    $language = normalize_language($language);
    $canonical = canonical_slug_for_route($sourceType, $slug);
    return post_slug_routes()[$sourceType][$canonical][$language] ?? $canonical;
}

function post_route_from_path(string $path): ?array
{
    $path = normalize_route_path($path);
    foreach (post_type_prefixes() as $sourceType => $prefixes) {
        foreach ($prefixes as $prefix) {
            $prefix = normalize_route_path($prefix);
            if (str_starts_with($path . '/', $prefix . '/')) {
                $slug = trim(substr($path, strlen($prefix)), '/');
                if ($slug !== '') {
                    return [
                        'source_type' => $sourceType,
                        'slug' => canonical_slug_for_route($sourceType, $slug),
                    ];
                }
            }
        }
    }

    return null;
}

function localized_post_path_values(string $language, string $sourceType, string $slug, ?string $fallbackPath = null): string
{
    $language = normalize_language($language);
    $sourceType = $sourceType ?: 'post';
    $prefixes = post_type_prefixes();
    if (!isset($prefixes[$sourceType])) {
        return localized_route_path($fallbackPath ?: ('/blog/' . $slug), $language);
    }

    return $prefixes[$sourceType][$language] . '/' . localized_slug_for_route($sourceType, $slug, $language);
}

function localized_post_path(array $post, ?string $language = null): string
{
    $language = normalize_language($language ?? ($post['language_code'] ?? DEFAULT_LANGUAGE));
    return localized_post_path_values(
        $language,
        (string) ($post['source_type'] ?? 'post'),
        (string) ($post['slug'] ?? ''),
        (string) ($post['path'] ?? '')
    );
}

function post_path_matches(array $post, string $language, string $path): bool
{
    $path = normalize_route_path($path);
    $sourceType = (string) ($post['source_type'] ?? 'post');
    $slug = (string) ($post['slug'] ?? '');
    $storedPath = normalize_route_path((string) ($post['path'] ?? ''));

    if ($storedPath === $path || normalize_route_path(localized_post_path($post, $language)) === $path) {
        return true;
    }

    $route = post_route_from_path($path);
    return $route !== null
        && $route['source_type'] === $sourceType
        && $route['slug'] === canonical_slug_for_route($sourceType, $slug);
}

function localized_route_path(string $path, string $language): string
{
    $language = normalize_language($language);
    $path = normalize_route_path($path);

    $pageKey = route_any_page_key_for_path($path);
    if ($pageKey !== null) {
        return route_page_path($pageKey, $language) ?? $path;
    }

    if (blog_index_path_matches($language, $path) || in_array($path, blog_index_routes(), true)) {
        return blog_index_path($language);
    }

    if (case_study_archive_path_matches($language, $path) || in_array($path, case_study_archive_routes(), true)) {
        return case_study_archive_path($language);
    }

    $route = post_route_from_path($path);
    if ($route !== null) {
        return localized_post_path_values($language, $route['source_type'], $route['slug'], $path);
    }

    return $path;
}

function localized_path(string $path, ?string $language = null): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }

    $language = normalize_language($language ?? ($_GET['lang'] ?? DEFAULT_LANGUAGE));
    $path = localized_route_path($path, $language);

    if ($language === DEFAULT_LANGUAGE) {
        return $path;
    }

    return language_prefix($language) . ($path === '/' ? '' : $path);
}

function current_request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    return '/' . trim($path, '/') ?: '/';
}

function detect_language_and_path(): array
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = '/' . trim($path, '/');
    $path = $path === '/' ? '/' : rtrim($path, '/');
    $segments = array_values(array_filter(explode('/', $path)));

    if ($segments && in_array($segments[0], SUPPORTED_LANGUAGES, true)) {
        $language = $segments[0];
        $remaining = array_slice($segments, 1);
        $cleanPath = $remaining ? '/' . implode('/', $remaining) : '/';
        return [$language, $cleanPath];
    }

    return [DEFAULT_LANGUAGE, $path];
}

function admin_language(): string
{
    return normalize_language($_POST['lang'] ?? $_GET['lang'] ?? DEFAULT_LANGUAGE);
}

function load_site(string $language = DEFAULT_LANGUAGE): array
{
    $language = normalize_language($language);
    $settings = [];
    $stmt = db()->prepare('SELECT setting_key, setting_value FROM settings WHERE language_code IN (?, ?) ORDER BY language_code = ? DESC');
    $stmt->execute([$language, DEFAULT_LANGUAGE, DEFAULT_LANGUAGE]);
    foreach ($stmt as $row) {
        $settings[$row['setting_key']] = repair_text_encoding($row['setting_value']);
    }

    $stmt = db()->prepare('SELECT nav_key, label, path, visible FROM navigation WHERE language_code = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$language]);
    $navigation = $stmt->fetchAll();
    if (!$navigation && $language !== DEFAULT_LANGUAGE) {
        $stmt->execute([DEFAULT_LANGUAGE]);
        $navigation = $stmt->fetchAll();
    }
    $navigation = array_map(fn ($item) => [
        'key' => $item['nav_key'],
        'label' => repair_text_encoding($item['label']),
        'path' => $item['path'],
        'visible' => (bool) $item['visible'],
    ], $navigation);

    $pages = [];
    $stmt = db()->prepare('SELECT * FROM pages WHERE language_code = ?');
    $stmt->execute([$language]);
    $pageRows = $stmt->fetchAll();
    if (!$pageRows && $language !== DEFAULT_LANGUAGE) {
        $stmt->execute([DEFAULT_LANGUAGE]);
        $pageRows = $stmt->fetchAll();
    }
    foreach ($pageRows as $row) {
        $extra = json_decode($row['extra_json'] ?: '{}', true);
        $extra = repair_data_encoding(is_array($extra) ? $extra : []);
        $pages[$row['page_key']] = array_merge([
            'path' => $row['path'],
            'title' => repair_text_encoding($row['title']),
            'summary' => repair_text_encoding($row['summary']),
            'body' => repair_text_encoding($row['body']),
            'ctaLabel' => $row['cta_label'] === null ? null : repair_text_encoding($row['cta_label']),
            'ctaHref' => $row['cta_href'],
            'secondaryCtaLabel' => $row['secondary_cta_label'] === null ? null : repair_text_encoding($row['secondary_cta_label']),
            'secondaryCtaHref' => $row['secondary_cta_href'],
        ], $extra);
    }
    $pages += legal_page_fallbacks($language);

    $stmt = db()->prepare('SELECT source_type, slug, path, source_url, title, post_date AS date, excerpt, body, published FROM posts WHERE language_code = ? ORDER BY post_date DESC, id DESC');
    $stmt->execute([$language]);
    $posts = repair_data_encoding($stmt->fetchAll());
    $posts = array_map(fn ($post) => array_merge($post, [
        'published' => (bool) $post['published'],
    ]), $posts);

    return compact('settings', 'navigation', 'pages', 'posts') + ['language' => $language];
}

function page_by_path(array $site, string $path): array
{
    $language = normalize_language($site['language'] ?? DEFAULT_LANGUAGE);
    $routeKey = route_page_key_for_path($language, $path);
    if ($routeKey !== null && isset($site['pages'][$routeKey])) {
        return [$routeKey, $site['pages'][$routeKey]];
    }

    if ($path === '/') {
        return ['home', $site['pages']['home'] ?? null];
    }

    foreach ($site['navigation'] as $item) {
        if ($item['path'] === $path && isset($site['pages'][$item['key']])) {
            return [$item['key'], $site['pages'][$item['key']]];
        }
    }

    foreach ($site['pages'] as $key => $page) {
        if (($page['path'] ?? '') === $path) {
            return [$key, $page];
        }
    }

    return [null, null];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        $language = admin_language();
        http_response_code(403);
        echo render_error_page(error_page_text($language, 'rejected_title'), error_page_text($language, 'rejected_message'), $language);
        exit;
    }
}

function current_admin(): ?array
{
    if (empty($_SESSION['admin_user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username FROM admin_users WHERE id = ?');
    $stmt->execute([$_SESSION['admin_user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION = [];
        session_destroy();
        return null;
    }

    return $user;
}

function require_admin(): array
{
    $user = current_admin();
    if (!$user) {
        redirect('/admin/login');
    }

    return $user;
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function method(): string
{
    return $_SERVER['REQUEST_METHOD'] === 'HEAD' ? 'GET' : $_SERVER['REQUEST_METHOD'];
}

function format_date(?string $value): string
{
    if (!$value) {
        return '';
    }

    $timestamp = strtotime($value);
    if (!$timestamp) {
        return $value;
    }

    return date('d.m.Y', $timestamp);
}

function field_name(array $segments): string
{
    return implode('__', $segments);
}

function label_from_key(string $key): string
{
    $label = preg_replace('/([a-z])([A-Z])/', '$1 $2', $key) ?? $key;
    $label = str_replace('_', ' ', $label);
    return ucwords($label);
}

function render_editable_fields($value, array $path): string
{
    if (is_array($value) && array_is_list($value)) {
        $html = '';
        foreach ($value as $index => $item) {
            $html .= '<fieldset><legend>' . e(label_from_key(end($path) ?: 'Item')) . ' ' . ($index + 1) . '</legend>';
            $html .= render_editable_fields($item, [...$path, (string) $index]);
            $html .= '</fieldset>';
        }
        return $html;
    }

    if (is_array($value)) {
        $html = '';
        foreach ($value as $key => $nested) {
            if ($key === 'path' || $key === 'key') {
                $html .= '<label>' . e(label_from_key($key)) . '<input value="' . e($nested) . '" disabled></label>';
                continue;
            }

            if (is_array($nested)) {
                $html .= '<fieldset><legend>' . e(label_from_key((string) $key)) . '</legend>';
                $html .= render_editable_fields($nested, [...$path, (string) $key]);
                $html .= '</fieldset>';
                continue;
            }

            $html .= render_field((string) $key, $nested, [...$path, (string) $key]);
        }
        return $html;
    }

    return '';
}

function render_field(string $key, $value, array $path): string
{
    $name = field_name($path);
    $label = e(label_from_key($key));

    if (is_bool($value)) {
        return '<label class="checkbox"><input name="' . e($name) . '" type="checkbox" ' . ($value ? 'checked' : '') . '> ' . $label . '</label>';
    }

    $text = (string) ($value ?? '');
    $isLong = strlen($text) > 90 || str_contains($text, "\n") || preg_match('/(body|text|summary|intro|note|footer)/i', $key);

    if ($isLong) {
        return '<label>' . $label . '<textarea name="' . e($name) . '" rows="7">' . e($text) . '</textarea></label>';
    }

    return '<label>' . $label . '<input name="' . e($name) . '" value="' . e($text) . '"></label>';
}

function apply_editable_fields($current, array $path)
{
    if (is_array($current) && array_is_list($current)) {
        return array_map(fn ($item, $index) => apply_editable_fields($item, [...$path, (string) $index]), $current, array_keys($current));
    }

    if (is_array($current)) {
        $next = [];
        foreach ($current as $key => $value) {
            if ($key === 'path' || $key === 'key') {
                $next[$key] = $value;
                continue;
            }
            $next[$key] = apply_editable_fields($value, [...$path, (string) $key]);
        }
        return $next;
    }

    $name = field_name($path);
    if (is_bool($current)) {
        return isset($_POST[$name]);
    }

    return array_key_exists($name, $_POST) ? (string) $_POST[$name] : $current;
}

if (PHP_SAPI !== 'cli') {
    send_security_headers();
    enforce_request_hardening();
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (str_starts_with('/' . trim($requestPath, '/'), '/admin')) {
        start_secure_session();
    }
}
