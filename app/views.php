<?php

declare(strict_types=1);

function public_layout(array $site, string $body, array $options = []): string
{
    $settings = $site['settings'];
    $language = $site['language'] ?? DEFAULT_LANGUAGE;
    $title = ($options['title'] ?? null)
        ? $options['title'] . ' | ' . $settings['brandName']
        : $settings['brandName'] . ' | ' . $settings['tagline'];
    $description = $options['description'] ?? $settings['footerText'];

    return '<!doctype html>
<html lang="' . e($language) . '">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . e($title) . '</title>
    <meta name="description" content="' . e($description) . '">
    <link rel="icon" href="/assets/logo.png">
    <link rel="stylesheet" href="/styles.css">
  </head>
  <body>
    ' . render_header($site, $options['active'] ?? '') . '
    <main>' . $body . '</main>
    ' . render_footer($site) . '
  </body>
</html>';
}

function render_header(array $site, string $active): string
{
    $settings = $site['settings'];
    $language = $site['language'] ?? DEFAULT_LANGUAGE;
    $links = '';

    foreach ($site['navigation'] as $item) {
        if (!$item['visible']) {
            continue;
        }
        $class = $active === $item['key'] ? ' class="active"' : '';
        $links .= '<a' . $class . ' href="' . e(localized_path($item['path'], $language)) . '">' . e($item['label']) . '</a>';
    }

    $languageLinks = '';
    foreach (SUPPORTED_LANGUAGES as $code) {
        $class = $language === $code ? ' class="active"' : '';
        $languageLinks .= '<a' . $class . ' href="' . e(localized_path('/', $code)) . '">' . strtoupper(e($code)) . '</a>';
    }

    return '<header class="site-header">
    <a class="brand" href="' . e(localized_path('/', $language)) . '" aria-label="' . e($settings['brandName']) . '">
      <img src="/assets/logo.png" alt="" width="52" height="46">
      <span>
        <strong>' . e($settings['brandName']) . '</strong>
        <small>' . e($settings['tagline']) . '</small>
      </span>
    </a>
    <nav aria-label="Navigatie principala">' . $links . '</nav>
    <nav class="language-nav" aria-label="Language">' . $languageLinks . '</nav>
    <a class="header-contact" href="tel:' . e(preg_replace('/\s+/', '', $settings['phone'])) . '">' . e($settings['phone']) . '</a>
  </header>';
}

function render_footer(array $site): string
{
    $settings = $site['settings'];

    return '<footer class="site-footer">
    <div class="footer-grid">
      <section>
        <img src="/assets/logo.png" alt="' . e($settings['brandName']) . '" width="68" height="61">
        <p>' . e($settings['footerText']) . '</p>
      </section>
      <section>
        <h2>Program</h2>
        <p>' . e($settings['workingHours']) . '</p>
        <p>' . e($settings['closedHours']) . '</p>
      </section>
      <section>
        <h2>Contact</h2>
        <p><a href="mailto:' . e($settings['email']) . '">' . e($settings['email']) . '</a></p>
        <p><a href="tel:' . e(preg_replace('/\s+/', '', $settings['phone'])) . '">' . e($settings['phone']) . '</a></p>
        <p>' . e($settings['address']) . '</p>
      </section>
      <section>
        <h2>Linkuri utile</h2>
        <p><a href="' . e($settings['anpcUrl']) . '" rel="noopener">' . e($settings['anpcLabel']) . '</a></p>
        <img class="anpc" src="/assets/anpc.webp" alt="ANPC">
      </section>
    </div>
  </footer>';
}

function render_text_cards(array $items): string
{
    $html = '';
    foreach ($items as $item) {
        $html .= '<article class="text-card">
        <h3>' . e($item['title'] ?? '') . '</h3>
        <p>' . e($item['text'] ?? '') . '</p>
      </article>';
    }
    return $html;
}

function render_home(array $site): string
{
    $page = $site['pages']['home'];
    $services = '';

    foreach ($page['services'] ?? [] as $service) {
        $services .= '<article class="service-card">
        <img src="' . e($service['image'] ?? '') . '" alt="">
        <div>
          <h3>' . e($service['title'] ?? '') . '</h3>
          <p>' . e($service['text'] ?? '') . '</p>
        </div>
      </article>';
    }

    $body = '<section class="hero home-hero">
    <div class="hero-copy">
      <p class="eyebrow">' . e($site['settings']['tagline']) . '</p>
      <h1>' . e($page['title']) . '</h1>
      <p>' . e($page['summary']) . '</p>
      <div class="hero-actions">
        <a class="button" href="' . e(localized_path($page['ctaHref'], $site['language'])) . '">' . e($page['ctaLabel']) . '</a>
        <a class="button button-secondary" href="' . e(localized_path($page['secondaryCtaHref'], $site['language'])) . '">' . e($page['secondaryCtaLabel']) . '</a>
      </div>
    </div>
  </section>
  <section class="content-band">
    <div class="prose">' . render_markdown($page['body']) . '</div>
  </section>
  <section class="content-band muted">
    <div class="section-heading">
      <p class="eyebrow">Avantaje</p>
      <h2>' . e($page['featuresTitle'] ?? 'Avantaje') . '</h2>
    </div>
    <div class="card-grid">' . render_text_cards($page['features'] ?? []) . '</div>
  </section>
  <section class="content-band">
    <div class="section-heading">
      <p class="eyebrow">Servicii</p>
      <h2>' . e($page['servicesTitle'] ?? 'Servicii') . '</h2>
      <p>' . e($page['servicesIntro'] ?? '') . '</p>
    </div>
    <div class="service-grid">' . $services . '</div>
  </section>
  <section class="content-band muted">
    <div class="section-heading">
      <p class="eyebrow">Proces simplu</p>
      <h2>' . e($page['requirementsTitle'] ?? 'De ce ai nevoie') . '</h2>
    </div>
    <div class="card-grid">' . render_text_cards($page['requirements'] ?? []) . '</div>
  </section>';

    return public_layout($site, $body, [
        'active' => 'home',
        'title' => $page['title'],
        'description' => $page['summary'],
    ]);
}

function render_generic_page(array $site, string $key, array $page): string
{
    $extra = '';
    foreach (['values', 'products', 'features'] as $collection) {
        if (!empty($page[$collection]) && is_array($page[$collection])) {
            $extra .= '<div class="card-grid">' . render_text_cards($page[$collection]) . '</div>';
        }
    }

    $body = '<section class="page-hero">
    <div>
      <p class="eyebrow">' . e($site['settings']['brandName']) . '</p>
      <h1>' . e($page['title']) . '</h1>
      <p>' . e($page['summary']) . '</p>
    </div>
  </section>
  <section class="content-band">
    <div class="prose">' . render_markdown($page['body']) . '</div>
    ' . $extra . '
  </section>';

    return public_layout($site, $body, [
        'active' => $key,
        'title' => $page['title'],
        'description' => $page['summary'],
    ]);
}

function render_contact(array $site): string
{
    $settings = $site['settings'];
    $page = $site['pages']['contact'];
    $subject = rawurlencode('Solicitare Local Capital');

    $body = '<section class="page-hero">
    <div>
      <p class="eyebrow">Contact</p>
      <h1>' . e($page['title']) . '</h1>
      <p>' . e($page['summary']) . '</p>
    </div>
  </section>
  <section class="contact-layout">
    <div class="prose">' . render_markdown($page['body']) . '</div>
    <div class="contact-panel">
      <h2>' . e($page['formTitle'] ?? 'Trimite un mesaj') . '</h2>
      <form action="mailto:' . e($settings['email']) . '?subject=' . e($subject) . '" method="post" enctype="text/plain">
        <label>Nume <input name="Nume" autocomplete="name" required></label>
        <label>Email <input name="Email" type="email" autocomplete="email" required></label>
        <label>Subiect <input name="Subiect" required></label>
        <label>Mesaj <textarea name="Mesaj" rows="6" required></textarea></label>
        <button class="button" type="submit">Trimite</button>
      </form>
      <p class="privacy">' . e($page['privacyNote'] ?? '') . '</p>
    </div>
    <aside class="contact-facts">
      <h2>Date de contact</h2>
      <p><strong>Telefon</strong><br><a href="tel:' . e(preg_replace('/\s+/', '', $settings['phone'])) . '">' . e($settings['phone']) . '</a></p>
      <p><strong>Email</strong><br><a href="mailto:' . e($settings['email']) . '">' . e($settings['email']) . '</a></p>
      <p><strong>Adresa</strong><br>' . e($settings['address']) . '</p>
      <p><strong>Program</strong><br>' . e($settings['workingHours']) . '<br>' . e($settings['closedHours']) . '</p>
    </aside>
  </section>';

    return public_layout($site, $body, [
        'active' => 'contact',
        'title' => $page['title'],
        'description' => $page['summary'],
    ]);
}

function render_blog(array $site): string
{
    $posts = '';
    foreach ($site['posts'] as $post) {
        if (!$post['published']) {
            continue;
        }
        $posts .= '<article class="post-card">
        <p class="eyebrow">' . e(format_date($post['date'])) . '</p>
        <h2><a href="' . e(localized_path($post['path'] ?: '/blog/' . $post['slug'], $site['language'])) . '">' . e($post['title']) . '</a></h2>
        <p>' . e($post['excerpt'] ?: plain_text($post['body'])) . '</p>
      </article>';
    }

    $body = '<section class="page-hero">
    <div>
      <p class="eyebrow">Local Capital</p>
      <h1>Noutati si informatii utile</h1>
      <p>Articole scurte despre creditare, documente utile si decizii financiare mai clare.</p>
    </div>
  </section>
  <section class="content-band">
    <div class="post-list">' . ($posts ?: '<p>Nu exista articole publicate momentan.</p>') . '</div>
  </section>';

    return public_layout($site, $body, [
        'active' => 'blog',
        'title' => 'Noutati',
        'description' => 'Noutati si informatii utile Local Capital',
    ]);
}

function render_post(array $site, string $slug): ?string
{
    foreach ($site['posts'] as $post) {
        if ($post['slug'] !== $slug || !$post['published']) {
            continue;
        }

        $body = '<article class="article">
    <p class="eyebrow">' . e(format_date($post['date'])) . '</p>
    <h1>' . e($post['title']) . '</h1>
    <p class="lead">' . e($post['excerpt']) . '</p>
    <div class="prose">' . render_markdown($post['body']) . '</div>
  </article>';

        return public_layout($site, $body, [
            'active' => 'blog',
            'title' => $post['title'],
            'description' => $post['excerpt'] ?: plain_text($post['body']),
        ]);
    }

    return null;
}

function render_post_by_path(array $site, string $path): ?string
{
    foreach ($site['posts'] as $post) {
        if (($post['path'] ?? '') !== $path || !$post['published']) {
            continue;
        }

        return render_post($site, $post['slug']);
    }

    return null;
}

function render_error_page(string $title, string $message): string
{
    return '<!doctype html>
<html lang="ro">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . e($title) . '</title>
    <link rel="stylesheet" href="/styles.css">
  </head>
  <body>
    <main class="error-page">
      <h1>' . e($title) . '</h1>
      <p>' . e($message) . '</p>
      <a class="button" href="/">Inapoi la prima pagina</a>
    </main>
  </body>
</html>';
}
