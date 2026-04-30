<?php

declare(strict_types=1);

const ROOT_DIR = __DIR__ . '/..';
const DEFAULT_LANGUAGE = 'ro';
const SUPPORTED_LANGUAGES = ['ro', 'en', 'hu'];

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

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $config = app_config()['app'];
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

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
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $csp = [
        "default-src 'self'",
        "script-src 'nonce-" . $nonce . "'",
        "style-src 'self'",
        "img-src 'self' data:",
        "font-src 'self'",
        "connect-src 'self'",
        "object-src 'none'",
        "form-action 'self'",
        "base-uri 'none'",
        "frame-ancestors 'none'",
    ];
    if ($secure) {
        $csp[] = 'upgrade-insecure-requests';
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header('Cross-Origin-Resource-Policy: same-origin');
    header('Origin-Agent-Cluster: ?1');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('Content-Security-Policy: ' . implode('; ', $csp));

    if ($secure) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function e(mixed $value): string
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

function repair_data_encoding(mixed $value): mixed
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
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    return hash_hmac('sha256', $ip, form_secret());
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

function localized_path(string $path, ?string $language = null): string
{
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }

    $language = normalize_language($language ?? ($_GET['lang'] ?? DEFAULT_LANGUAGE));
    $path = '/' . ltrim($path ?: '/', '/');
    $path = $path === '//' ? '/' : $path;

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
        http_response_code(403);
        echo render_error_page('Cerere respinsă', 'Tokenul de securitate nu este valid.');
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

function render_editable_fields(mixed $value, array $path): string
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

function render_field(string $key, mixed $value, array $path): string
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

function apply_editable_fields(mixed $current, array $path): mixed
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
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    if (str_starts_with('/' . trim($requestPath, '/'), '/admin')) {
        start_secure_session();
    }
}
