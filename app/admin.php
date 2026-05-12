<?php

declare(strict_types=1);

function admin_url(string $path, string $language): string
{
    return $path . '?lang=' . rawurlencode($language);
}

function admin_url_with_params(string $path, array $params = []): string
{
    $query = http_build_query($params);
    return $path . ($query !== '' ? '?' . $query : '');
}

function admin_current_path(): string
{
    $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/admin'), PHP_URL_PATH);
    return is_string($path) && $path !== '' ? $path : '/admin';
}

function admin_count_badge(int $count): string
{
    return $count > 0 ? '<span class="admin-menu-badge">' . e((string) $count) . '</span>' : '';
}

function admin_language_label(string $language): string
{
    return [
        'ro' => 'Română',
        'en' => 'English',
        'hu' => 'Magyar',
    ][$language] ?? strtoupper($language);
}

function admin_user_count(): int
{
    $stmt = db()->query('SELECT COUNT(*) FROM admin_users');
    return (int) $stmt->fetchColumn();
}

function admin_import_pending_count(): int
{
    try {
        $count = 0;
        foreach (content_update_statuses() as $status) {
            if (in_array($status['status'], ['pending', 'queued'], true)) {
                $count++;
            }
        }

        return $count;
    } catch (Throwable $error) {
        return 0;
    }
}

function admin_sidebar_menu(array $site, string $currentPath): string
{
    $language = $site['language'] ?? DEFAULT_LANGUAGE;
    $messageCount = function_exists('unread_contact_message_count') ? unread_contact_message_count() : 0;
    $anafCount = function_exists('unread_anaf_consent_count') ? unread_anaf_consent_count() : 0;
    $adminUserCount = admin_user_count();
    $importPendingCount = admin_import_pending_count();
    $postCount = count($site['posts'] ?? []);
    $pageCount = count($site['pages'] ?? []);

    $groups = [
        'Principal' => [
            ['label' => 'Panou', 'href' => admin_url('/admin', $language), 'match' => ['/admin'], 'exact' => true],
        ],
        'Conținut' => [
            ['label' => 'Pagini', 'href' => admin_url('/admin/pages', $language), 'match' => ['/admin/pages'], 'badge' => $pageCount],
            ['label' => 'Articole', 'href' => admin_url('/admin/posts', $language), 'match' => ['/admin/posts'], 'badge' => $postCount],
            ['label' => 'Setări site', 'href' => admin_url('/admin/settings', $language), 'match' => ['/admin/settings']],
        ],
        'Operațional' => [
            ['label' => 'Acorduri ANAF', 'href' => admin_url('/admin/anaf-consents', $language), 'match' => ['/admin/anaf-consents'], 'badge' => $anafCount, 'strong' => true],
            ['label' => 'Mesaje contact', 'href' => admin_url('/admin/messages', $language), 'match' => ['/admin/messages'], 'badge' => $messageCount],
        ],
        'Sistem' => [
            ['label' => 'Importuri', 'href' => admin_url('/admin/imports', $language), 'match' => ['/admin/imports'], 'badge' => $importPendingCount],
            ['label' => 'Utilizatori', 'href' => admin_url('/admin/users', $language), 'match' => ['/admin/users'], 'badge' => $adminUserCount],
        ],
        'Instrumente' => [
            ['label' => 'Inventar linkuri', 'href' => admin_url('/admin/links', $language), 'match' => ['/admin/links']],
            ['label' => 'Site public', 'href' => localized_path('/', $language), 'match' => [], 'external' => true],
        ],
    ];

    $html = '';
    foreach ($groups as $groupLabel => $items) {
        $html .= '<div class="admin-menu-section"><p>' . e($groupLabel) . '</p>';
        foreach ($items as $item) {
            $active = false;
            foreach ($item['match'] as $matchPath) {
                $active = !empty($item['exact']) ? $currentPath === $matchPath : str_starts_with($currentPath, $matchPath);
                if ($active) {
                    break;
                }
            }
            $class = trim(($active ? 'active ' : '') . (!empty($item['strong']) ? 'important' : ''));
            $html .= '<a' . ($class !== '' ? ' class="' . e($class) . '"' : '') . ' href="' . e($item['href']) . '"' . (!empty($item['external']) ? ' target="_blank" rel="noopener"' : '') . '><span>' . e($item['label']) . '</span>' . admin_count_badge((int) ($item['badge'] ?? 0)) . '</a>';
        }
        $html .= '</div>';
    }

    return $html;
}

function admin_layout(array $site, array $admin, string $body, string $title = 'Admin'): string
{
    if (!headers_sent()) {
        header('Cache-Control: no-store, private');
    }

    $language = $site['language'] ?? DEFAULT_LANGUAGE;
    $cssVersion = asset_version('/styles.css');
    $currentPath = admin_current_path();
    $sidebarMenu = admin_sidebar_menu($site, $currentPath);
    $languageLinks = '';
    foreach (SUPPORTED_LANGUAGES as $code) {
        $class = $language === $code ? ' class="active"' : '';
        $languageLinks .= '<a' . $class . ' href="' . e(admin_url($currentPath, $code)) . '">' . strtoupper(e($code)) . '</a>';
    }

    return '<!doctype html>
<html lang="' . e($language) . '">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . e($title) . ' | ' . e($site['settings']['brandName']) . '</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="stylesheet" href="/styles.css?v=' . e($cssVersion) . '">
  </head>
  <body class="admin-body">
    <div class="admin-shell">
      <aside class="admin-sidebar">
        <a class="admin-brand" href="/admin?lang=' . e($language) . '">
          <img src="/assets/logo.png" srcset="' . e(logo_webp_srcset()) . '" sizes="42px" alt="" width="42" height="38">
          <span><strong>Local Capital</strong><small>Admin panel</small></span>
        </a>
        <nav class="admin-menu" aria-label="Admin principal">' . $sidebarMenu . '</nav>
      </aside>
      <div class="admin-workspace">
        <header class="admin-topbar">
          <div class="admin-topbar-title">
            <p>Administrare</p>
            <h1>' . e($title) . '</h1>
          </div>
          <div class="admin-topbar-actions">
            <div class="admin-language-switcher" aria-label="Limbi admin">' . $languageLinks . '</div>
            <span class="admin-user">' . e($admin['username'] ?? 'admin') . '</span>
            <form action="/admin/logout" method="post">
              <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
              <button type="submit">Ieșire</button>
            </form>
          </div>
        </header>
        <main id="admin-content" class="admin-main">' . $body . '</main>
      </div>
    </div>
  </body>
</html>';
}

function login_page(string $message = ''): string
{
    $cssVersion = asset_version('/styles.css');

    return '<!doctype html>
<html lang="ro">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login | Local Capital</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="stylesheet" href="/styles.css?v=' . e($cssVersion) . '">
  </head>
  <body class="login-page">
    <main class="login-panel">
      <img src="/assets/logo.png" srcset="' . e(logo_webp_srcset()) . '" sizes="78px" alt="Local Capital" width="78" height="70">
      <h1>Admin Local Capital</h1>
      ' . ($message ? '<p class="form-message">' . e($message) . '</p>' : '') . '
      <form action="/admin/login" method="post"' . recaptcha_form_attributes('admin_login') . '>
        ' . render_recaptcha_field('admin_login') . '
        <label>Utilizator <input name="username" autocomplete="username" required></label>
        <label>Parolă <input name="password" type="password" autocomplete="current-password" required></label>
        <button class="button" type="submit">Autentificare</button>
      </form>
    </main>
    ' . render_recaptcha_script() . '
  </body>
</html>';
}

function ensure_admin_login_attempts_table(): void
{
    static $ready = false;
    if ($ready) {
        return;
    }

    db()->exec('CREATE TABLE IF NOT EXISTS admin_login_attempts (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        attempt_key CHAR(64) NOT NULL,
        ip_hash CHAR(64) NOT NULL,
        username_hash CHAR(64) NOT NULL,
        attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_admin_login_attempt_key_time (attempt_key, attempted_at),
        KEY idx_admin_login_ip_time (ip_hash, attempted_at),
        KEY idx_admin_login_attempt_time (attempted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    db()->exec('CREATE TABLE IF NOT EXISTS admin_login_bans (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        ip_hash CHAR(64) NOT NULL,
        banned_until DATETIME NOT NULL,
        reason VARCHAR(120) NOT NULL DEFAULT \'admin_login\',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_admin_login_ban_ip (ip_hash),
        KEY idx_admin_login_banned_until (banned_until)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $ready = true;
}

function admin_login_attempt_key(string $username): string
{
    return hash_hmac('sha256', strtolower(trim($username)) . '|' . client_ip_hash(), form_secret());
}

function admin_username_hash(string $username): string
{
    return hash_hmac('sha256', strtolower(trim($username)), form_secret());
}

function admin_login_banned_until(): ?string
{
    ensure_admin_login_attempts_table();

    db()->exec('DELETE FROM admin_login_bans WHERE banned_until <= NOW()');

    $stmt = db()->prepare('SELECT banned_until FROM admin_login_bans WHERE ip_hash = ? AND banned_until > NOW() LIMIT 1');
    $stmt->execute([client_ip_hash()]);
    $bannedUntil = $stmt->fetchColumn();

    return is_string($bannedUntil) && $bannedUntil !== '' ? $bannedUntil : null;
}

function admin_login_failure_count(string $username): int
{
    ensure_admin_login_attempts_table();

    $windowMinutes = security_policy('admin_login_window_minutes', 15);
    $stmt = db()->prepare('SELECT COUNT(*) FROM admin_login_attempts WHERE (attempt_key = ? OR ip_hash = ?) AND attempted_at > DATE_SUB(NOW(), INTERVAL ' . $windowMinutes . ' MINUTE)');
    $stmt->execute([admin_login_attempt_key($username), client_ip_hash()]);

    return (int) $stmt->fetchColumn();
}

function admin_login_rate_limited(string $username): bool
{
    ensure_admin_login_attempts_table();

    db()->exec('DELETE FROM admin_login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');

    if (admin_login_banned_until() !== null) {
        return true;
    }

    return admin_login_failure_count($username) >= security_policy('admin_login_max_failures', 5);
}

function admin_login_fail2ban_log(string $event, string $username = '', string $message = ''): void
{
    $event = preg_replace('/[^a-z0-9_-]/i', '', $event) ?: 'admin-login-event';
    $message = preg_replace('/[\r\n]+/', ' ', $message);

    error_log(sprintf(
        'LOCALCAPITAL_FAIL2BAN event=%s ip=%s username_hash=%s message=%s',
        $event,
        client_ip(),
        admin_username_hash($username),
        trim($message)
    ));
}

function ban_admin_login_ip(string $username): void
{
    $banMinutes = security_policy('admin_login_ban_minutes', 30);
    $stmt = db()->prepare('INSERT INTO admin_login_bans (ip_hash, banned_until, reason) VALUES (?, DATE_ADD(NOW(), INTERVAL ' . $banMinutes . ' MINUTE), \'admin_login\') ON DUPLICATE KEY UPDATE banned_until = VALUES(banned_until), reason = VALUES(reason)');
    $stmt->execute([client_ip_hash()]);

    admin_login_fail2ban_log('admin-login-ban', $username, 'duration_minutes=' . $banMinutes);
}

function record_admin_login_failure(string $username): void
{
    ensure_admin_login_attempts_table();

    $stmt = db()->prepare('INSERT INTO admin_login_attempts (attempt_key, ip_hash, username_hash) VALUES (?, ?, ?)');
    $stmt->execute([admin_login_attempt_key($username), client_ip_hash(), admin_username_hash($username)]);

    admin_login_fail2ban_log('admin-login-failure', $username);

    if (admin_login_failure_count($username) >= security_policy('admin_login_max_failures', 5)) {
        ban_admin_login_ip($username);
    }
}

function clear_admin_login_failures(string $username): void
{
    ensure_admin_login_attempts_table();

    $stmt = db()->prepare('DELETE FROM admin_login_attempts WHERE attempt_key = ? OR ip_hash = ?');
    $stmt->execute([admin_login_attempt_key($username), client_ip_hash()]);
}

function handle_login_post(): void
{
    $username = trim((string) ($_POST['username'] ?? ''));

    if (admin_login_rate_limited($username)) {
        http_response_code(429);
        $bannedUntil = admin_login_banned_until();
        admin_login_fail2ban_log('admin-login-blocked', $username, $bannedUntil ? 'banned_until=' . $bannedUntil : 'rate_limited=1');
        echo login_page('Prea multe încercări. Accesul la autentificare este blocat temporar.');
        return;
    }

    $recaptcha = recaptcha_verify('admin_login');
    if (empty($recaptcha['ok'])) {
        record_admin_login_failure($username);
        http_response_code(403);
        echo login_page('Verificarea de securitate nu a reușit. Reîncarcă pagina și încearcă din nou.');
        return;
    }

    $stmt = db()->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
        record_admin_login_failure($username);
        http_response_code(401);
        echo login_page('Datele de autentificare nu sunt corecte.');
        return;
    }

    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = (int) $user['id'];
    clear_admin_login_failures($username);
    csrf_token();
    redirect('/admin');
}

function render_admin_dashboard(array $site, array $admin): string
{
    $messageCount = unread_contact_message_count();
    $anafCount = function_exists('unread_anaf_consent_count') ? unread_anaf_consent_count() : 0;
    $adminUserCount = admin_user_count();
    $publishedPosts = 0;
    foreach ($site['posts'] as $post) {
        if (!empty($post['published'])) {
            $publishedPosts++;
        }
    }

    $recentPosts = '';
    foreach (array_slice($site['posts'], 0, 6) as $post) {
        $recentPosts .= '<li><a href="/admin/posts/' . e($post['slug']) . '?lang=' . e($site['language']) . '">' . e($post['title']) . '</a><span>' . e(admin_post_type_label((string) ($post['source_type'] ?? 'post'))) . '</span></li>';
    }

    $body = '<section class="admin-overview">
    <div>
      <p class="eyebrow">Bun venit</p>
      <h2>Admin Local Capital</h2>
      <p>Lucrezi acum pe textele în limba ' . e(admin_language_label($site['language'])) . '.</p>
    </div>
    <div class="admin-quick-actions">
      <a class="button" href="/admin/pages?lang=' . e($site['language']) . '">Pagini</a>
      <a class="button button-secondary" href="/admin/posts?lang=' . e($site['language']) . '">Articole</a>
      <a class="button button-secondary" href="/admin/anaf-consents/new?lang=' . e($site['language']) . '">Generează link ANAF</a>
      <a class="button button-secondary" href="/admin/users?lang=' . e($site['language']) . '">Utilizatori</a>
    </div>
  </section>
  <section class="admin-stat-grid" aria-label="Rezumat admin">
    <a class="admin-stat" href="/admin/pages?lang=' . e($site['language']) . '"><span>Pagini</span><strong>' . e((string) count($site['pages'])) . '</strong></a>
    <a class="admin-stat" href="/admin/posts?lang=' . e($site['language']) . '"><span>Publicate</span><strong>' . e((string) $publishedPosts) . '</strong></a>
    <a class="admin-stat" href="/admin/messages?lang=' . e($site['language']) . '"><span>Mesaje noi</span><strong>' . e((string) $messageCount) . '</strong></a>
    <a class="admin-stat important" href="/admin/anaf-consents?lang=' . e($site['language']) . '"><span>Acorduri ANAF</span><strong>' . e((string) $anafCount) . '</strong></a>
  </section>
  <section class="admin-grid compact">
    <article class="admin-card">
      <h2>Conținut</h2>
      <div class="admin-action-list compact">
        <a href="/admin/pages?lang=' . e($site['language']) . '"><strong>Pagini</strong><span>Editare rapidă pe RO / EN / HU.</span></a>
        <a href="/admin/posts?lang=' . e($site['language']) . '"><strong>Articole și servicii</strong><span>Listă compactă cu status, tip și dată.</span></a>
        <a href="/admin/settings?lang=' . e($site['language']) . '"><strong>Setări site</strong><span>Date de contact și navigație.</span></a>
      </div>
    </article>
    <article class="admin-card">
      <h2>Recente</h2>
      <ul class="admin-list">' . ($recentPosts ?: '<li>Nu există articole.</li>') . '</ul>
    </article>
    <article class="admin-card">
      <h2>Operațional</h2>
      <div class="admin-action-list compact">
        <a href="/admin/anaf-consents?lang=' . e($site['language']) . '"><strong>Acorduri ANAF</strong><span>Generează linkuri precompletate, vezi acceptările și descarcă PDF-uri.</span></a>
        <a href="/admin/messages?lang=' . e($site['language']) . '"><strong>Mesaje contact</strong><span>Verifică cererile noi trimise prin formularul public.</span></a>
        <a href="/admin/links?lang=' . e($site['language']) . '"><strong>Inventar linkuri</strong><span>Controlează linkurile importate și resursele externe.</span></a>
      </div>
    </article>
	    <article class="admin-card">
	      <h2>Sistem</h2>
	      <p class="admin-muted">Sunt ' . e((string) $adminUserCount) . ' utilizatori admin configurați.</p>
	      <a class="button button-secondary" href="/admin/imports?lang=' . e($site['language']) . '">Importuri</a>
	      <a class="button button-secondary" href="/admin/users?lang=' . e($site['language']) . '">Gestionează utilizatori</a>
	    </article>
	  </section>';

    return admin_layout($site, $admin, $body, 'Panou admin');
}

function admin_import_flash(): array
{
    $flash = $_SESSION['admin_import_flash'] ?? [];
    unset($_SESSION['admin_import_flash']);
    return is_array($flash) ? $flash : [];
}

function admin_import_status_label(string $status): string
{
    return [
        'ran' => 'Rulat',
        'pending' => 'Pending',
        'queued' => 'Va rula',
        'missing' => 'Lipsește',
    ][$status] ?? label_from_key($status);
}

function admin_import_status_note(string $status): string
{
    return [
        'ran' => 'Checksum identic cu ultima rulare.',
        'pending' => 'Fișier nou sau modificat.',
        'queued' => 'Se rulează după un import anterior modificat.',
        'missing' => 'Fișierul nu există pe disc.',
    ][$status] ?? '';
}

function admin_short_checksum(?string $checksum): string
{
    return $checksum ? substr($checksum, 0, 12) : '-';
}

function render_admin_import_result_rows(array $results): string
{
    if (!$results) {
        return '';
    }

    $items = '';
    foreach ($results as $result) {
        $items .= '<li><strong>' . e((string) ($result['file'] ?? 'import')) . '</strong><span>' . e(label_from_key((string) ($result['status'] ?? 'unknown'))) . '</span></li>';
    }

    return '<ul class="admin-import-result-list">' . $items . '</ul>';
}

function render_admin_imports(array $site, array $admin): string
{
    try {
        $statuses = content_update_statuses();
        $loadError = '';
    } catch (Throwable $error) {
        $statuses = [];
        $loadError = $error->getMessage();
    }

    $flash = admin_import_flash();
    $pendingCount = 0;
    $ranCount = 0;
    $missingCount = 0;
    $rows = '';

    foreach ($statuses as $status) {
        $state = (string) ($status['status'] ?? 'missing');
        if (in_array($state, ['pending', 'queued'], true)) {
            $pendingCount++;
        } elseif ($state === 'ran') {
            $ranCount++;
        } elseif ($state === 'missing') {
            $missingCount++;
        }

        $currentChecksum = is_string($status['checksum'] ?? null) ? $status['checksum'] : null;
        $previousChecksum = is_string($status['previous_checksum'] ?? null) ? $status['previous_checksum'] : null;
        $rows .= '<tr>
          <td><strong>' . e((string) ($status['file'] ?? 'import')) . '</strong><br><small>' . e((string) ($status['key'] ?? '')) . '</small></td>
          <td><span class="status-pill status-import-' . e($state) . '">' . e(admin_import_status_label($state)) . '</span><br><small>' . e(admin_import_status_note($state)) . '</small></td>
          <td><small>Actual: ' . e(admin_short_checksum($currentChecksum)) . '<br>Înregistrat: ' . e(admin_short_checksum($previousChecksum)) . '</small></td>
          <td>' . e((string) ($status['applied_at'] ?? '-')) . '</td>
        </tr>';
    }

    $message = '';
    if ($loadError !== '') {
        $message = '<p class="form-message error">Importurile nu pot fi citite: ' . e($loadError) . '</p>';
    } elseif ($flash) {
        $type = (string) ($flash['type'] ?? 'success');
        $message = '<div class="form-message ' . e($type) . '"><p>' . e((string) ($flash['message'] ?? '')) . '</p>' . render_admin_import_result_rows(is_array($flash['results'] ?? null) ? $flash['results'] : []) . '</div>';
    }

    $runDisabled = $pendingCount === 0 || $loadError !== '' ? ' disabled' : '';
    $forceDisabled = $loadError !== '' ? ' disabled' : '';
    $body = '<section class="admin-overview">
    <div>
      <p class="eyebrow">Sistem</p>
      <h2>Importuri și migrații de conținut</h2>
      <p>Rulează fișierele SQL urmărite în ordinea lor, similar cu un sistem de migration. Checksumurile sunt salvate după rulare, iar importurile modificate apar ca pending.</p>
    </div>
    <div class="admin-quick-actions">
      <form action="/admin/imports?lang=' . e($site['language']) . '" method="post">
        <input type="hidden" name="lang" value="' . e($site['language']) . '">
        <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
        <input type="hidden" name="action" value="run_pending">
        <button class="button" type="submit"' . $runDisabled . '>Rulează pending</button>
      </form>
      <form action="/admin/imports?lang=' . e($site['language']) . '" method="post">
        <input type="hidden" name="lang" value="' . e($site['language']) . '">
        <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
        <input type="hidden" name="action" value="force_all">
        <button class="button button-secondary" type="submit"' . $forceDisabled . '>Rulează tot din nou</button>
      </form>
    </div>
  </section>
  <section class="admin-stat-grid admin-import-stat-grid" aria-label="Rezumat importuri">
    <div class="admin-stat"><span>Pending</span><strong>' . e((string) $pendingCount) . '</strong></div>
    <div class="admin-stat"><span>Rulate</span><strong>' . e((string) $ranCount) . '</strong></div>
    <div class="admin-stat"><span>Lipsă</span><strong>' . e((string) $missingCount) . '</strong></div>
    <div class="admin-stat"><span>Total</span><strong>' . e((string) count($statuses)) . '</strong></div>
  </section>
  <section class="admin-card wide">
    <div class="admin-title-row">
      <div>
        <p class="eyebrow">Migration log</p>
        <h1>Status importuri</h1>
        <p class="admin-muted">Dacă un import din mijloc este modificat, importurile de după el sunt marcate „Va rula”, ca să refacă stratul final de conținut.</p>
      </div>
    </div>
    ' . $message . '
    <div class="table-wrap">
      <table class="admin-table admin-import-table">
        <thead><tr><th>Import</th><th>Status</th><th>Checksum</th><th>Ultima rulare</th></tr></thead>
        <tbody>' . ($rows ?: '<tr><td colspan="4">Nu există importuri configurate.</td></tr>') . '</tbody>
      </table>
    </div>
  </section>';

    return admin_layout($site, $admin, $body, 'Importuri');
}

function handle_admin_imports_post(array $site, array $admin): never
{
    $action = (string) ($_POST['action'] ?? 'run_pending');
    $force = $action === 'force_all';

    try {
        $results = apply_content_updates_with_lock($force, 30, 2);
        $applied = count(array_filter($results, static fn (array $result): bool => ($result['status'] ?? '') === 'applied'));
        $unchanged = count(array_filter($results, static fn (array $result): bool => ($result['status'] ?? '') === 'unchanged'));
        $_SESSION['admin_import_flash'] = [
            'type' => 'success',
            'message' => $force
                ? 'Toate importurile au fost rulate din nou.'
                : 'Importurile pending au fost rulate.',
            'results' => $results,
            'summary' => compact('applied', 'unchanged'),
        ];
    } catch (Throwable $error) {
        $_SESSION['admin_import_flash'] = [
            'type' => 'error',
            'message' => 'Importurile nu au putut fi rulate: ' . $error->getMessage(),
            'results' => [],
        ];
    }

    redirect('/admin/imports?lang=' . rawurlencode($site['language']));
}

function admin_post_type_label(string $sourceType): string
{
    return [
        'post' => 'Articol',
        'service' => 'Serviciu',
        'case_study' => 'Studiu de caz',
    ][$sourceType] ?? label_from_key($sourceType);
}

function render_admin_language_tabs(array $targets, string $currentLanguage, string $label = 'Limba textului'): string
{
    $links = '';
    foreach (SUPPORTED_LANGUAGES as $code) {
        $target = $targets[$code] ?? ['href' => admin_url(admin_current_path(), $code), 'exists' => true];
        $classes = ['admin-language-tab'];
        if ($code === $currentLanguage) {
            $classes[] = 'active';
        }
        if (empty($target['exists'])) {
            $classes[] = 'missing';
        }
        $suffix = empty($target['exists']) ? '<small>nou</small>' : '';
        $links .= '<a class="' . e(implode(' ', $classes)) . '" href="' . e((string) $target['href']) . '"><strong>' . strtoupper(e($code)) . '</strong><span>' . e(admin_language_label($code)) . '</span>' . $suffix . '</a>';
    }

    return '<div class="admin-language-tabs" aria-label="' . e($label) . '"><p>' . e($label) . '</p><div>' . $links . '</div></div>';
}

function admin_page_language_targets(string $key): array
{
    $targets = [];
    foreach (SUPPORTED_LANGUAGES as $code) {
        $targets[$code] = [
            'href' => admin_url('/admin/pages/' . $key, $code),
            'exists' => true,
        ];
    }

    return $targets;
}

function admin_post_language_targets(array $post, string $currentLanguage): array
{
    $slug = (string) ($post['slug'] ?? '');
    $sourceType = (string) ($post['source_type'] ?? 'post');
    $targets = [];
    foreach (SUPPORTED_LANGUAGES as $code) {
        $targets[$code] = [
            'href' => admin_url('/admin/posts/new', $code),
            'exists' => false,
        ];
    }

    if ($slug === '') {
        foreach (SUPPORTED_LANGUAGES as $code) {
            $targets[$code]['href'] = admin_url('/admin/posts/new', $code);
            $targets[$code]['exists'] = true;
        }
        return $targets;
    }

    $stmt = db()->prepare('SELECT language_code, slug FROM posts WHERE source_type = ? AND slug = ?');
    $stmt->execute([$sourceType, $slug]);
    foreach ($stmt->fetchAll() as $row) {
        $language = normalize_language($row['language_code'] ?? '');
        $targets[$language] = [
            'href' => admin_url('/admin/posts/' . (string) $row['slug'], $language),
            'exists' => true,
        ];
    }

    foreach (SUPPORTED_LANGUAGES as $code) {
        if (!empty($targets[$code]['exists'])) {
            continue;
        }
        $targets[$code]['href'] = admin_url_with_params('/admin/posts/new', [
            'lang' => $code,
            'copy_lang' => $currentLanguage,
            'copy_slug' => $slug,
            'source_type' => $sourceType,
        ]);
    }

    return $targets;
}

function render_pages_index(array $site, array $admin): string
{
    $rows = '';
    foreach ($site['pages'] as $key => $page) {
        $publicPath = localized_path((string) ($page['path'] ?? '/'), $site['language']);
        $rows .= '<tr>
          <td><strong>' . e($page['title'] ?? label_from_key((string) $key)) . '</strong><br><small>' . e($key) . '</small></td>
          <td><a href="' . e($publicPath) . '" target="_blank" rel="noopener">' . e($publicPath) . '</a></td>
          <td>' . render_admin_language_tabs(admin_page_language_targets((string) $key), $site['language'], 'Limbi') . '</td>
          <td><a class="button button-secondary" href="/admin/pages/' . e((string) $key) . '?lang=' . e($site['language']) . '">Editează</a></td>
        </tr>';
    }

    $body = '<section class="admin-card wide">
      <div class="admin-title-row">
        <div>
          <p class="eyebrow">Conținut</p>
          <h1>Pagini</h1>
          <p class="admin-muted">Editare compactă pentru textele paginilor, cu selector de limbă pe fiecare rând.</p>
        </div>
      </div>
      <div class="table-wrap">
        <table class="admin-table admin-compact-table">
          <thead><tr><th>Pagină</th><th>URL public</th><th>Limbi</th><th></th></tr></thead>
          <tbody>' . ($rows ?: '<tr><td colspan="4">Nu există pagini.</td></tr>') . '</tbody>
        </table>
      </div>
    </section>';

    return admin_layout($site, $admin, $body, 'Pagini');
}

function render_posts_index(array $site, array $admin): string
{
    $rows = '';
    foreach ($site['posts'] as $post) {
        $publicPath = localized_path(localized_post_path($post, $site['language']), $site['language']);
        $status = !empty($post['published']) ? 'published' : 'draft';
        $rows .= '<tr>
          <td><strong>' . e($post['title']) . '</strong><br><small>' . e($post['slug']) . '</small></td>
          <td>' . e(admin_post_type_label((string) ($post['source_type'] ?? 'post'))) . '<br><small>' . e(format_date($post['date'] ?? '')) . '</small></td>
          <td><span class="status-pill status-' . e($status) . '">' . (!empty($post['published']) ? 'publicat' : 'draft') . '</span></td>
          <td><a href="' . e($publicPath) . '" target="_blank" rel="noopener">' . e($publicPath) . '</a></td>
          <td>' . render_admin_language_tabs(admin_post_language_targets($post, $site['language']), $site['language'], 'Limbi') . '</td>
          <td><a class="button button-secondary" href="/admin/posts/' . e($post['slug']) . '?lang=' . e($site['language']) . '">Editează</a></td>
        </tr>';
    }

    $body = '<section class="admin-card wide">
      <div class="admin-title-row">
        <div>
          <p class="eyebrow">Conținut</p>
          <h1>Articole și servicii</h1>
          <p class="admin-muted">Status, tip, URL public și selector de limbă într-o singură listă.</p>
        </div>
        <a class="button" href="/admin/posts/new?lang=' . e($site['language']) . '">Articol nou</a>
      </div>
      <div class="table-wrap">
        <table class="admin-table admin-compact-table">
          <thead><tr><th>Titlu</th><th>Tip</th><th>Status</th><th>URL public</th><th>Limbi</th><th></th></tr></thead>
          <tbody>' . ($rows ?: '<tr><td colspan="6">Nu există articole.</td></tr>') . '</tbody>
        </table>
      </div>
    </section>';

    return admin_layout($site, $admin, $body, 'Articole');
}

function unread_contact_message_count(): int
{
    $stmt = db()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
    return (int) $stmt->fetchColumn();
}

function render_contact_messages(array $site, array $admin): string
{
    $stmt = db()->prepare('SELECT * FROM contact_messages WHERE language_code = ? ORDER BY status = "new" DESC, created_at DESC LIMIT 100');
    $stmt->execute([$site['language']]);
    $rows = repair_data_encoding($stmt->fetchAll());

    $items = '';
    foreach ($rows as $row) {
        $status = (string) $row['status'];
        $nextStatus = $status === 'archived' ? 'read' : ($status === 'new' ? 'read' : 'archived');
        $buttonLabel = $status === 'archived' ? 'Reactivare' : ($status === 'new' ? 'Marchează citit' : 'Arhivează');
        $phone = $row['phone'] ? '<br><a href="tel:' . e(preg_replace('/\s+/', '', $row['phone'])) . '">' . e($row['phone']) . '</a>' : '';
        $items .= '<tr>
          <td><span class="status-pill status-' . e($status) . '">' . e(label_from_key($status)) . '</span><br><small>' . e($row['created_at']) . '</small></td>
          <td><strong>' . e($row['name']) . '</strong><br><a href="mailto:' . e($row['email']) . '">' . e($row['email']) . '</a>' . $phone . '</td>
          <td><strong>' . e($row['subject']) . '</strong><p class="message-preview">' . e($row['message']) . '</p></td>
          <td>
            <form action="/admin/messages/' . e($row['id']) . '?lang=' . e($site['language']) . '" method="post">
              <input type="hidden" name="lang" value="' . e($site['language']) . '">
              <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
              <input type="hidden" name="status" value="' . e($nextStatus) . '">
              <button class="button button-secondary" type="submit">' . e($buttonLabel) . '</button>
            </form>
          </td>
        </tr>';
    }

    $body = '<section class="admin-card wide">
      <p class="eyebrow">Contact</p>
      <h1>Mesaje primite</h1>
      <p>Ultimele 100 de mesaje trimise prin formularul de contact pentru limba ' . e(strtoupper($site['language'])) . '.</p>
      <div class="table-wrap">
        <table class="admin-table messages-table">
          <thead><tr><th>Status</th><th>Contact</th><th>Mesaj</th><th>Acțiune</th></tr></thead>
          <tbody>' . ($items ?: '<tr><td colspan="4">Nu există mesaje pentru această limbă.</td></tr>') . '</tbody>
        </table>
      </div>
    </section>';

    return admin_layout($site, $admin, $body, 'Mesaje primite');
}

function render_links_inventory(array $site, array $admin): string
{
    $stmt = db()->prepare('SELECT source_url, href, label, is_internal FROM site_links WHERE language_code = ? ORDER BY source_url ASC, is_internal DESC, href ASC');
    $stmt->execute([$site['language']]);
    $rows = repair_data_encoding($stmt->fetchAll());

    $items = '';
    foreach ($rows as $row) {
        $type = $row['is_internal'] ? 'intern' : 'extern';
        $items .= '<tr>
          <td><a href="' . e($row['source_url']) . '" rel="noopener">' . e($row['source_url']) . '</a></td>
          <td><a href="' . e($row['href']) . '" rel="noopener">' . e($row['href']) . '</a></td>
          <td>' . e($row['label']) . '</td>
          <td>' . e($type) . '</td>
        </tr>';
    }

    $body = '<section class="admin-card wide">
      <p class="eyebrow">WordPress import</p>
      <h1>Link inventory</h1>
      <p>' . count($rows) . ' linkuri importate pentru limba ' . e(strtoupper($site['language'])) . '.</p>
      <div class="table-wrap">
        <table class="admin-table">
          <thead><tr><th>Sursa</th><th>Link</th><th>Text</th><th>Tip</th></tr></thead>
          <tbody>' . ($items ?: '<tr><td colspan="4">Nu există linkuri importate.</td></tr>') . '</tbody>
        </table>
      </div>
    </section>';

    return admin_layout($site, $admin, $body, 'Link inventory');
}

function render_settings_form(array $site, array $admin): string
{
    $body = '<section class="admin-card wide">
    <h1>Setări site</h1>
    <form class="admin-form" action="/admin/settings?lang=' . e($site['language']) . '" method="post">
      <input type="hidden" name="lang" value="' . e($site['language']) . '">
      <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
      ' . render_editable_fields($site['settings'], ['settings']) . '
      <h2>Navigație</h2>
      ' . render_editable_fields($site['navigation'], ['navigation']) . '
      <button class="button" type="submit">Salvează</button>
    </form>
  </section>';

    return admin_layout($site, $admin, $body, 'Setări site');
}

function render_page_editor(array $site, array $admin, string $key): ?string
{
    if (empty($site['pages'][$key])) {
        return null;
    }

    $page = $site['pages'][$key];
    $body = '<section class="admin-card wide">
    <div class="admin-title-row">
      <div>
        <p class="eyebrow">Pagină</p>
        <h1>' . e($page['title']) . '</h1>
      </div>
      ' . render_admin_language_tabs(admin_page_language_targets($key), $site['language']) . '
    </div>
    <form class="admin-form" action="/admin/pages/' . e($key) . '?lang=' . e($site['language']) . '" method="post">
      <input type="hidden" name="lang" value="' . e($site['language']) . '">
      <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
      ' . render_editable_fields($page, ['page']) . '
      <button class="button" type="submit">Salvează pagina</button>
    </form>
  </section>';

    return admin_layout($site, $admin, $body, $page['title']);
}

function admin_allowed_post_source_types(): array
{
    return ['post', 'service', 'case_study'];
}

function admin_normalize_post_source_type(string $sourceType): string
{
    return in_array($sourceType, admin_allowed_post_source_types(), true) ? $sourceType : 'post';
}

function admin_post_copy_source(): ?array
{
    $copyLanguage = normalize_language((string) ($_GET['copy_lang'] ?? DEFAULT_LANGUAGE));
    $copySlug = trim((string) ($_GET['copy_slug'] ?? ''));
    $sourceType = admin_normalize_post_source_type((string) ($_GET['source_type'] ?? 'post'));
    if ($copySlug === '') {
        return null;
    }

    $stmt = db()->prepare('SELECT source_type, slug, title, post_date AS date, excerpt, body, published FROM posts WHERE language_code = ? AND source_type = ? AND slug = ? LIMIT 1');
    $stmt->execute([$copyLanguage, $sourceType, $copySlug]);
    $source = $stmt->fetch();
    if (!$source) {
        return null;
    }

    return repair_data_encoding(array_merge($source, [
        'published' => false,
    ]));
}

function render_post_editor(array $site, array $admin, string $slug, string $message = '', ?array $oldPost = null): ?string
{
    $post = [
        'slug' => '',
        'source_type' => 'post',
        'title' => '',
        'date' => date('Y-m-d'),
        'excerpt' => '',
        'body' => '',
        'published' => false,
    ];

    if ($slug === 'new') {
        $copy = admin_post_copy_source();
        if ($copy !== null) {
            $post = array_merge($post, $copy);
        }
    }

    if ($slug !== 'new') {
        $post = null;
        foreach ($site['posts'] as $candidate) {
            if ($candidate['slug'] === $slug) {
                $post = $candidate;
                break;
            }
        }
        if (!$post) {
            return null;
        }
    }

    if ($oldPost !== null) {
        $post = array_merge($post, $oldPost);
    }

    $sourceType = admin_normalize_post_source_type((string) ($post['source_type'] ?? 'post'));
    $slugReadonly = $sourceType !== 'post' && $slug !== 'new' ? ' readonly' : '';
    $languageTabs = render_admin_language_tabs(admin_post_language_targets($post, $site['language']), $site['language']);

    $body = '<section class="admin-card wide">
    <div class="admin-title-row">
      <div>
        <p class="eyebrow">' . ($slug === 'new' ? 'Articol nou' : 'Editare articol') . '</p>
        <h1>' . e($post['title'] ?: 'Articol nou') . '</h1>
        <p class="admin-muted">' . e(admin_post_type_label($sourceType)) . ' · ' . e(admin_language_label($site['language'])) . '</p>
      </div>
      ' . $languageTabs . '
    </div>
    ' . ($message ? '<p class="form-message error">' . e($message) . '</p>' : '') . '
    <form class="admin-form" action="/admin/posts/' . e($slug) . '?lang=' . e($site['language']) . '" method="post">
      <input type="hidden" name="lang" value="' . e($site['language']) . '">
      <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
      <input type="hidden" name="source_type" value="' . e($sourceType) . '">
      <label>Slug <input name="slug" value="' . e($post['slug']) . '" placeholder="titlu-articol"' . $slugReadonly . '></label>
      <label>Titlu <input name="title" value="' . e($post['title']) . '" required></label>
      <label>Data <input name="date" type="date" value="' . e($post['date']) . '" required></label>
      <label>Rezumat <textarea name="excerpt" rows="3">' . e($post['excerpt']) . '</textarea></label>
      <label>Conținut <textarea name="body" rows="14">' . e($post['body']) . '</textarea></label>
      <label class="checkbox"><input name="published" type="checkbox" ' . ($post['published'] ? 'checked' : '') . '> Publicat</label>
      <button class="button" type="submit">Salvează articol</button>
    </form>
  </section>';

    return admin_layout($site, $admin, $body, $post['title'] ?: 'Articol nou');
}

function save_settings(array $site): void
{
    $settings = apply_editable_fields($site['settings'], ['settings']);
    $stmt = db()->prepare('UPDATE settings SET setting_value = ? WHERE language_code = ? AND setting_key = ?');
    foreach ($settings as $key => $value) {
        $stmt->execute([(string) $value, $site['language'], $key]);
    }

    $navigation = apply_editable_fields($site['navigation'], ['navigation']);
    $stmt = db()->prepare('UPDATE navigation SET label = ?, visible = ? WHERE language_code = ? AND nav_key = ?');
    foreach ($navigation as $item) {
        $stmt->execute([$item['label'], $item['visible'] ? 1 : 0, $site['language'], $item['key']]);
    }
}

function save_page(array $site, string $key): void
{
    if (empty($site['pages'][$key])) {
        http_response_code(404);
        echo render_error_page(error_page_text($site['language'], 'not_found_title'), error_page_text($site['language'], 'not_found_message'), $site['language']);
        exit;
    }

    $page = apply_editable_fields($site['pages'][$key], ['page']);
    $baseKeys = ['path', 'title', 'summary', 'body', 'ctaLabel', 'ctaHref', 'secondaryCtaLabel', 'secondaryCtaHref'];
    $extra = array_diff_key($page, array_flip($baseKeys));

    $stmt = db()->prepare('INSERT INTO pages (language_code, page_key, path, title, summary, body, cta_label, cta_href, secondary_cta_label, secondary_cta_href, extra_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE path = VALUES(path), title = VALUES(title), summary = VALUES(summary), body = VALUES(body), cta_label = VALUES(cta_label), cta_href = VALUES(cta_href), secondary_cta_label = VALUES(secondary_cta_label), secondary_cta_href = VALUES(secondary_cta_href), extra_json = VALUES(extra_json), updated_at = CURRENT_TIMESTAMP');
    $stmt->execute([
        $site['language'],
        $key,
        $page['path'] ?? '/',
        $page['title'],
        $page['summary'],
        $page['body'],
        $page['ctaLabel'] ?? null,
        $page['ctaHref'] ?? null,
        $page['secondaryCtaLabel'] ?? null,
        $page['secondaryCtaHref'] ?? null,
        json_encode($extra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);
}

function post_slug_conflicts(string $language, string $sourceType, string $slug, ?string $excludeSlug = null): bool
{
    $sql = 'SELECT COUNT(*) FROM posts WHERE language_code = ? AND source_type = ? AND slug = ?';
    $params = [$language, $sourceType, $slug];

    if ($excludeSlug !== null) {
        $sql .= ' AND slug <> ?';
        $params[] = $excludeSlug;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn() > 0;
}

function save_post(array $site, array $admin, string $originalSlug): string
{
    $language = admin_language();
    $title = trim((string) ($_POST['title'] ?? 'Articol fără titlu'));
    $slug = slugify(trim((string) ($_POST['slug'] ?? '')) ?: $title);
    if ($slug === '') {
        $slug = 'articol-' . date('YmdHis');
    }
    $existing = null;
    $sourceType = admin_normalize_post_source_type((string) ($_POST['source_type'] ?? 'post'));
    if ($originalSlug !== 'new') {
        $stmt = db()->prepare('SELECT source_type, path FROM posts WHERE language_code = ? AND slug = ? LIMIT 1');
        $stmt->execute([$language, $originalSlug]);
        $existing = $stmt->fetch() ?: null;
        if (!$existing) {
            http_response_code(404);
            echo render_error_page(error_page_text($language, 'not_found_title'), error_page_text($language, 'not_found_message'), $language);
            exit;
        }
        $sourceType = $existing['source_type'] ?? 'post';
        if (($existing['source_type'] ?? 'post') !== 'post') {
            $slug = $originalSlug;
        }
    }
    $post = [
        'slug' => $slug,
        'source_type' => $sourceType,
        'title' => $title,
        'date' => $_POST['date'] ?? date('Y-m-d'),
        'excerpt' => $_POST['excerpt'] ?: plain_text((string) ($_POST['body'] ?? '')),
        'body' => $_POST['body'] ?? '',
        'published' => isset($_POST['published']),
    ];

    $excludeSlug = $originalSlug === 'new' ? null : $originalSlug;
    if (post_slug_conflicts($language, $sourceType, $post['slug'], $excludeSlug)) {
        http_response_code(422);
        echo render_post_editor($site, $admin, $originalSlug, 'Slugul este deja folosit de un alt articol. Alege un slug unic.', $post);
        exit;
    }

    if ($originalSlug === 'new') {
        $stmt = db()->prepare('INSERT INTO posts (language_code, source_type, slug, path, source_url, title, post_date, excerpt, body, published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$language, $sourceType, $post['slug'], localized_post_path_values($language, $sourceType, $post['slug']), null, $post['title'], $post['date'], $post['excerpt'], $post['body'], $post['published'] ? 1 : 0]);
    } else {
        $path = localized_post_path_values($language, $sourceType, $post['slug'], (string) ($existing['path'] ?? ''));
        $stmt = db()->prepare('UPDATE posts SET slug = ?, path = ?, title = ?, post_date = ?, excerpt = ?, body = ?, published = ?, updated_at = CURRENT_TIMESTAMP WHERE language_code = ? AND source_type = ? AND slug = ?');
        $stmt->execute([$post['slug'], $path, $post['title'], $post['date'], $post['excerpt'], $post['body'], $post['published'] ? 1 : 0, $language, $sourceType, $originalSlug]);
    }

    return $post['slug'];
}

function update_contact_message_status(int $id): void
{
    $status = (string) ($_POST['status'] ?? 'read');
    if (!in_array($status, ['new', 'read', 'archived'], true)) {
        $status = 'read';
    }

    $stmt = db()->prepare('UPDATE contact_messages SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$status, $id]);
}

function admin_validate_username(string $username): ?string
{
    if (strlen($username) < 3 || strlen($username) > 120) {
        return 'Utilizatorul trebuie să aibă între 3 și 120 de caractere.';
    }

    if (!preg_match('/\A[a-zA-Z0-9._@-]+\z/', $username)) {
        return 'Utilizatorul poate conține doar litere, cifre, punct, underscore, cratimă sau @.';
    }

    return null;
}

function admin_validate_password(string $password, bool $required): ?string
{
    if ($password === '' && !$required) {
        return null;
    }

    if (strlen($password) < 12) {
        return 'Parola trebuie să aibă cel puțin 12 caractere.';
    }

    return null;
}

function admin_user_flash_message(): string
{
    return [
        'created' => 'Utilizatorul a fost creat.',
        'updated' => 'Utilizatorul a fost actualizat.',
        'deleted' => 'Utilizatorul a fost șters.',
    ][(string) ($_GET['ok'] ?? '')] ?? '';
}

function render_admin_users(array $site, array $admin, string $message = '', string $messageType = 'success'): string
{
    $stmt = db()->query('SELECT id, username, created_at, updated_at FROM admin_users ORDER BY username ASC');
    $users = $stmt->fetchAll();
    $message = $message ?: admin_user_flash_message();

    $rows = '';
    foreach ($users as $user) {
        $id = (int) $user['id'];
        $isCurrent = $id === (int) ($admin['id'] ?? 0);
        $deleteControl = $isCurrent
            ? '<span class="admin-muted">cont curent</span>'
            : '<form action="/admin/users/' . e((string) $id) . '?lang=' . e($site['language']) . '" method="post">
                <input type="hidden" name="lang" value="' . e($site['language']) . '">
                <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
                <input type="hidden" name="action" value="delete">
                <button class="button button-danger" type="submit">Șterge</button>
              </form>';

        $rows .= '<tr>
          <td><strong>' . e($user['username']) . '</strong>' . ($isCurrent ? '<br><small>autentificat acum</small>' : '') . '</td>
          <td><small>Creat: ' . e($user['created_at']) . '<br>Actualizat: ' . e($user['updated_at']) . '</small></td>
          <td>
            <form class="admin-inline-form" action="/admin/users/' . e((string) $id) . '?lang=' . e($site['language']) . '" method="post">
              <input type="hidden" name="lang" value="' . e($site['language']) . '">
              <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
              <input type="hidden" name="action" value="update">
              <label>Utilizator <input name="username" value="' . e($user['username']) . '" required></label>
              <label>Parolă nouă <input name="password" type="password" autocomplete="new-password" placeholder="lasă gol pentru neschimbat"></label>
              <button class="button button-secondary" type="submit">Salvează</button>
            </form>
          </td>
          <td>' . $deleteControl . '</td>
        </tr>';
    }

    $body = '<section class="admin-card wide">
      <div class="admin-title-row">
        <div>
          <p class="eyebrow">Sistem</p>
          <h1>Utilizatori admin</h1>
          <p class="admin-muted">Creează conturi noi, schimbă numele sau resetează parola unui utilizator existent.</p>
        </div>
      </div>
      ' . ($message ? '<p class="form-message ' . e($messageType) . '">' . e($message) . '</p>' : '') . '
      <section class="admin-subsection first">
        <h2>Utilizator nou</h2>
        <form class="admin-form admin-create-user-form" action="/admin/users?lang=' . e($site['language']) . '" method="post">
          <input type="hidden" name="lang" value="' . e($site['language']) . '">
          <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
          <input type="hidden" name="action" value="create">
          <label>Utilizator <input name="username" autocomplete="username" required></label>
          <label>Parolă <input name="password" type="password" autocomplete="new-password" required></label>
          <label>Confirmare parolă <input name="password_confirm" type="password" autocomplete="new-password" required></label>
          <button class="button" type="submit">Creează utilizator</button>
        </form>
      </section>
      <section class="admin-subsection">
        <h2>Conturi existente</h2>
        <div class="table-wrap">
          <table class="admin-table admin-compact-table">
            <thead><tr><th>Utilizator</th><th>Istoric</th><th>Editare rapidă</th><th></th></tr></thead>
            <tbody>' . ($rows ?: '<tr><td colspan="4">Nu există utilizatori.</td></tr>') . '</tbody>
          </table>
        </div>
      </section>
    </section>';

    return admin_layout($site, $admin, $body, 'Utilizatori admin');
}

function fail_admin_user_request(array $site, array $admin, string $message): never
{
    http_response_code(422);
    echo render_admin_users($site, $admin, $message, 'error');
    exit;
}

function redirect_admin_users(string $language, string $ok): never
{
    redirect('/admin/users?lang=' . rawurlencode($language) . '&ok=' . rawurlencode($ok));
}

function admin_user_duplicate_error(PDOException $error): bool
{
    return (string) $error->getCode() === '23000';
}

function create_admin_user_from_post(array $site, array $admin): never
{
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    $error = admin_validate_username($username) ?: admin_validate_password($password, true);
    if ($error !== null) {
        fail_admin_user_request($site, $admin, $error);
    }

    if (!hash_equals($password, $passwordConfirm)) {
        fail_admin_user_request($site, $admin, 'Confirmarea parolei nu corespunde.');
    }

    try {
        $stmt = db()->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
    } catch (PDOException $error) {
        if (!admin_user_duplicate_error($error)) {
            throw $error;
        }
        fail_admin_user_request($site, $admin, 'Există deja un utilizator cu acest nume.');
    }

    redirect_admin_users($site['language'], 'created');
}

function update_admin_user_from_post(array $site, array $admin, int $id): never
{
    $stmt = db()->prepare('SELECT id FROM admin_users WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        fail_admin_user_request($site, $admin, 'Utilizatorul nu există.');
    }

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $error = admin_validate_username($username) ?: admin_validate_password($password, false);
    if ($error !== null) {
        fail_admin_user_request($site, $admin, $error);
    }

    try {
        if ($password !== '') {
            $stmt = db()->prepare('UPDATE admin_users SET username = ?, password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = db()->prepare('UPDATE admin_users SET username = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$username, $id]);
        }
    } catch (PDOException $error) {
        if (!admin_user_duplicate_error($error)) {
            throw $error;
        }
        fail_admin_user_request($site, $admin, 'Există deja un utilizator cu acest nume.');
    }

    redirect_admin_users($site['language'], 'updated');
}

function delete_admin_user_from_post(array $site, array $admin, int $id): never
{
    if ($id === (int) ($admin['id'] ?? 0)) {
        fail_admin_user_request($site, $admin, 'Nu poți șterge utilizatorul cu care ești autentificat.');
    }

    if (admin_user_count() <= 1) {
        fail_admin_user_request($site, $admin, 'Trebuie să rămână cel puțin un utilizator admin.');
    }

    $stmt = db()->prepare('DELETE FROM admin_users WHERE id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        fail_admin_user_request($site, $admin, 'Utilizatorul nu există.');
    }

    redirect_admin_users($site['language'], 'deleted');
}

function handle_admin_user_post(array $site, array $admin, ?int $id = null): never
{
    $action = (string) ($_POST['action'] ?? ($id === null ? 'create' : 'update'));

    if ($action === 'create' && $id === null) {
        create_admin_user_from_post($site, $admin);
    }

    if ($id !== null && $action === 'update') {
        update_admin_user_from_post($site, $admin, $id);
    }

    if ($id !== null && $action === 'delete') {
        delete_admin_user_from_post($site, $admin, $id);
    }

    fail_admin_user_request($site, $admin, 'Acțiunea cerută nu este validă.');
}
