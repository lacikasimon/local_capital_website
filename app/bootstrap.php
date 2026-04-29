<?php

declare(strict_types=1);

const ROOT_DIR = __DIR__ . '/..';
const DEFAULT_LANGUAGE = 'ro';
const SUPPORTED_LANGUAGES = ['ro', 'en', 'hu'];

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

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'none'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; form-action 'self' mailto:; base-uri 'none'; frame-ancestors 'none'");

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    if ($secure) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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
    if (strlen($text) <= $length) {
        return $text;
    }

    return trim(substr($text, 0, max(0, $length - 3))) . '...';
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
        $settings[$row['setting_key']] = $row['setting_value'];
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
        'label' => $item['label'],
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
        $pages[$row['page_key']] = array_merge([
            'path' => $row['path'],
            'title' => $row['title'],
            'summary' => $row['summary'],
            'body' => $row['body'],
            'ctaLabel' => $row['cta_label'],
            'ctaHref' => $row['cta_href'],
            'secondaryCtaLabel' => $row['secondary_cta_label'],
            'secondaryCtaHref' => $row['secondary_cta_href'],
        ], is_array($extra) ? $extra : []);
    }

    $stmt = db()->prepare('SELECT source_type, slug, path, source_url, title, post_date AS date, excerpt, body, published FROM posts WHERE language_code = ? ORDER BY post_date DESC, id DESC');
    $stmt->execute([$language]);
    $posts = $stmt->fetchAll();
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
        echo render_error_page('Cerere respinsa', 'Tokenul de securitate nu este valid.');
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
    start_secure_session();
}
