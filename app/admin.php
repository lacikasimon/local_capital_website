<?php

declare(strict_types=1);

function admin_layout(array $site, array $admin, string $body, string $title = 'Admin'): string
{
    $language = $site['language'] ?? DEFAULT_LANGUAGE;
    $cssVersion = asset_version('/styles.css');
    $languageLinks = '';
    foreach (SUPPORTED_LANGUAGES as $code) {
        $class = $language === $code ? ' class="active"' : '';
        $languageLinks .= '<a' . $class . ' href="/admin?lang=' . e($code) . '">' . strtoupper(e($code)) . '</a>';
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
    <header class="admin-header">
      <a class="brand" href="/admin">
        <img src="/assets/logo.png" alt="" width="44" height="40">
        <span><strong>Admin</strong><small>' . e($site['settings']['brandName']) . '</small></span>
      </a>
      <nav>
        <a href="/admin?lang=' . e($language) . '">Panou</a>
        <a href="' . e(localized_path('/', $language)) . '">Site</a>
        ' . $languageLinks . '
        <form action="/admin/logout" method="post">
          <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
          <button type="submit">Ieșire</button>
        </form>
      </nav>
    </header>
    <main class="admin-main">' . $body . '</main>
    ' . render_recaptcha_script() . '
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
      <img src="/assets/logo.png" alt="Local Capital" width="78" height="70">
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
    $pages = '';
    foreach ($site['pages'] as $key => $page) {
        $pages .= '<li><a href="/admin/pages/' . e($key) . '?lang=' . e($site['language']) . '">' . e($page['title']) . '</a></li>';
    }

    $posts = '';
    foreach ($site['posts'] as $post) {
        $posts .= '<li><a href="/admin/posts/' . e($post['slug']) . '?lang=' . e($site['language']) . '">' . e($post['title']) . '</a><span>' . ($post['published'] ? 'publicat' : 'draft') . '</span></li>';
    }

    $body = '<section class="admin-grid">
    <article class="admin-card">
      <h1>Panou admin</h1>
      <p>Editează textele site-ului, datele companiei și articolele publicate.</p>
      <a class="button" href="/admin/settings?lang=' . e($site['language']) . '">Setări site</a>
      <a class="button button-secondary" href="/admin/links?lang=' . e($site['language']) . '">Link inventory</a>
      <a class="button button-secondary" href="/admin/messages?lang=' . e($site['language']) . '">Mesaje' . ($messageCount ? ' (' . $messageCount . ')' : '') . '</a>
    </article>
    <article class="admin-card">
      <h2>Pagini</h2>
      <ul class="admin-list">' . $pages . '</ul>
    </article>
    <article class="admin-card">
      <h2>Articole</h2>
      <ul class="admin-list">' . ($posts ?: '<li>Nu există articole.</li>') . '</ul>
      <a class="button button-secondary" href="/admin/posts/new?lang=' . e($site['language']) . '">Articol nou</a>
    </article>
  </section>';

    return admin_layout($site, $admin, $body, 'Panou admin');
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
    <p class="eyebrow">Pagină</p>
    <h1>' . e($page['title']) . '</h1>
    <form class="admin-form" action="/admin/pages/' . e($key) . '?lang=' . e($site['language']) . '" method="post">
      <input type="hidden" name="lang" value="' . e($site['language']) . '">
      <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
      ' . render_editable_fields($page, ['page']) . '
      <button class="button" type="submit">Salvează pagina</button>
    </form>
  </section>';

    return admin_layout($site, $admin, $body, $page['title']);
}

function render_post_editor(array $site, array $admin, string $slug, string $message = '', ?array $oldPost = null): ?string
{
    $post = [
        'slug' => '',
        'title' => '',
        'date' => date('Y-m-d'),
        'excerpt' => '',
        'body' => '',
        'published' => false,
    ];

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

    $body = '<section class="admin-card wide">
    <p class="eyebrow">' . ($slug === 'new' ? 'Articol nou' : 'Editare articol') . '</p>
    <h1>' . e($post['title'] ?: 'Articol nou') . '</h1>
    ' . ($message ? '<p class="form-message error">' . e($message) . '</p>' : '') . '
    <form class="admin-form" action="/admin/posts/' . e($slug) . '?lang=' . e($site['language']) . '" method="post">
      <input type="hidden" name="lang" value="' . e($site['language']) . '">
      <input type="hidden" name="csrf" value="' . e(csrf_token()) . '">
      <label>Slug <input name="slug" value="' . e($post['slug']) . '" placeholder="titlu-articol"></label>
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

    $stmt = db()->prepare('UPDATE pages SET title = ?, summary = ?, body = ?, cta_label = ?, cta_href = ?, secondary_cta_label = ?, secondary_cta_href = ?, extra_json = ?, updated_at = CURRENT_TIMESTAMP WHERE language_code = ? AND page_key = ?');
    $stmt->execute([
        $page['title'],
        $page['summary'],
        $page['body'],
        $page['ctaLabel'] ?? null,
        $page['ctaHref'] ?? null,
        $page['secondaryCtaLabel'] ?? null,
        $page['secondaryCtaHref'] ?? null,
        json_encode($extra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        $site['language'],
        $key,
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
    $sourceType = 'post';
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
        $stmt->execute([$language, 'post', $post['slug'], '/blog/' . $post['slug'], null, $post['title'], $post['date'], $post['excerpt'], $post['body'], $post['published'] ? 1 : 0]);
    } else {
        $path = $sourceType === 'post' ? '/blog/' . $post['slug'] : ($existing['path'] ?? '');
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
