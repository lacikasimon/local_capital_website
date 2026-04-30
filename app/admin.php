<?php

declare(strict_types=1);

function admin_layout(array $site, array $admin, string $body, string $title = 'Admin'): string
{
    $language = $site['language'] ?? DEFAULT_LANGUAGE;
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
    <link rel="stylesheet" href="/styles.css">
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
  </body>
</html>';
}

function login_page(string $message = ''): string
{
    return '<!doctype html>
<html lang="ro">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login | Local Capital</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="stylesheet" href="/styles.css">
  </head>
  <body class="login-page">
    <main class="login-panel">
      <img src="/assets/logo.png" alt="Local Capital" width="78" height="70">
      <h1>Admin Local Capital</h1>
      ' . ($message ? '<p class="form-message">' . e($message) . '</p>' : '') . '
      <form action="/admin/login" method="post">
        <label>Utilizator <input name="username" autocomplete="username" required></label>
        <label>Parolă <input name="password" type="password" autocomplete="current-password" required></label>
        <button class="button" type="submit">Autentificare</button>
      </form>
    </main>
  </body>
</html>';
}

function handle_login_post(): void
{
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] > 10) {
        http_response_code(429);
        echo login_page('Prea multe încercări. Te rugăm să aștepți câteva minute.');
        return;
    }

    $stmt = db()->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = ?');
    $stmt->execute([$_POST['username'] ?? '']);
    $user = $stmt->fetch();

    if (!$user || !password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
        http_response_code(401);
        echo login_page('Datele de autentificare nu sunt corecte.');
        return;
    }

    session_regenerate_id(true);
    $_SESSION['admin_user_id'] = (int) $user['id'];
    $_SESSION['login_attempts'] = 0;
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

function render_post_editor(array $site, array $admin, string $slug): ?string
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

    $body = '<section class="admin-card wide">
    <p class="eyebrow">' . ($slug === 'new' ? 'Articol nou' : 'Editare articol') . '</p>
    <h1>' . e($post['title'] ?: 'Articol nou') . '</h1>
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
        echo render_error_page('Pagina nu a fost găsită', 'Adresa cerută nu există.');
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

function save_post(string $originalSlug): string
{
    $language = admin_language();
    $title = trim((string) ($_POST['title'] ?? 'Articol fără titlu'));
    $slug = slugify(trim((string) ($_POST['slug'] ?? '')) ?: $title);
    $existing = null;
    if ($originalSlug !== 'new') {
        $stmt = db()->prepare('SELECT source_type, path FROM posts WHERE language_code = ? AND slug = ? LIMIT 1');
        $stmt->execute([$language, $originalSlug]);
        $existing = $stmt->fetch() ?: null;
        if (!$existing) {
            http_response_code(404);
            echo render_error_page('Pagina nu a fost găsită', 'Articolul cerut nu există.');
            exit;
        }
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
        'published' => isset($_POST['published']) ? 1 : 0,
    ];

    if ($originalSlug === 'new') {
        $stmt = db()->prepare('INSERT INTO posts (language_code, source_type, slug, path, source_url, title, post_date, excerpt, body, published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$language, 'post', $post['slug'], '/blog/' . $post['slug'], null, $post['title'], $post['date'], $post['excerpt'], $post['body'], $post['published']]);
    } else {
        $sourceType = $existing['source_type'] ?? 'post';
        $path = $sourceType === 'post' ? '/blog/' . $post['slug'] : ($existing['path'] ?? '');
        $stmt = db()->prepare('UPDATE posts SET slug = ?, path = ?, title = ?, post_date = ?, excerpt = ?, body = ?, published = ?, updated_at = CURRENT_TIMESTAMP WHERE language_code = ? AND source_type = ? AND slug = ?');
        $stmt->execute([$post['slug'], $path, $post['title'], $post['date'], $post['excerpt'], $post['body'], $post['published'], $language, $sourceType, $originalSlug]);
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
