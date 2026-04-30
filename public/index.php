<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';
require __DIR__ . '/../app/views.php';
require __DIR__ . '/../app/admin.php';

[$language, $path] = detect_language_and_path();
$method = method();

try {
    if ($path === '/admin/login' && $method === 'GET') {
        echo login_page();
        exit;
    }

    if ($path === '/admin/login' && $method === 'POST') {
        handle_login_post();
        exit;
    }

    $adminLang = admin_language();
    $site = load_site($adminLang);

    if (str_starts_with($path, '/admin')) {
        $admin = require_admin();

        if ($path === '/admin/logout' && $method === 'POST') {
            verify_csrf();
            $_SESSION = [];
            session_destroy();
            redirect('/admin/login');
        }

        if ($method === 'POST') {
            verify_csrf();

            if ($path === '/admin/settings') {
                save_settings($site);
                redirect('/admin?lang=' . $adminLang . '&ok=settings');
            }

            if (preg_match('#^/admin/pages/([a-z0-9-]+)$#', $path, $match)) {
                save_page($site, $match[1]);
                redirect('/admin/pages/' . $match[1] . '?lang=' . $adminLang . '&ok=1');
            }

            if (preg_match('#^/admin/posts/([a-z0-9-]+|new)$#', $path, $match)) {
                $slug = save_post($match[1]);
                redirect('/admin/posts/' . $slug . '?lang=' . $adminLang . '&ok=1');
            }
        }

        if ($path === '/admin') {
            echo render_admin_dashboard($site, $admin);
            exit;
        }

        if ($path === '/admin/settings') {
            echo render_settings_form($site, $admin);
            exit;
        }

        if ($path === '/admin/links') {
            echo render_links_inventory($site, $admin);
            exit;
        }

        if (preg_match('#^/admin/pages/([a-z0-9-]+)$#', $path, $match)) {
            $html = render_page_editor($site, $admin, $match[1]);
            if ($html) {
                echo $html;
                exit;
            }
        }

        if (preg_match('#^/admin/posts/([a-z0-9-]+|new)$#', $path, $match)) {
            $html = render_post_editor($site, $admin, $match[1]);
            if ($html) {
                echo $html;
                exit;
            }
        }

        http_response_code(404);
        echo render_error_page('Pagina nu a fost gasita', 'Adresa ceruta nu exista.');
        exit;
    }

    $site = load_site($language);

    if ($method !== 'GET') {
        http_response_code(405);
        echo render_error_page('Metoda nepermisa', 'Aceasta pagina accepta doar citire.');
        exit;
    }

    if ($path === '/blog') {
        echo render_blog($site);
        exit;
    }

    if (preg_match('#^/blog/([a-z0-9-]+)$#', $path, $match)) {
        $html = render_post($site, $match[1]);
        if ($html) {
            echo $html;
            exit;
        }
    }

    $postByPath = render_post_by_path($site, $path);
    if ($postByPath) {
        echo $postByPath;
        exit;
    }

    if (preg_match('#^/finlon-case-study-category/([a-z0-9-]+)$#', $path, $match)) {
        echo render_case_study_archive($site, $match[1]);
        exit;
    }

    [$key, $page] = page_by_path($site, $path);
    if (!$page) {
        http_response_code(404);
        echo render_error_page('Pagina nu a fost gasita', 'Adresa ceruta nu exista.');
        exit;
    }

    if ($key === 'home') {
        echo render_home($site);
    } elseif ($key === 'contact') {
        echo render_contact($site);
    } else {
        echo render_generic_page($site, $key, $page);
    }
} catch (Throwable $error) {
    $debug = app_config()['app']['debug'] ?? false;
    http_response_code(500);
    echo render_error_page('Eroare', $debug ? $error->getMessage() : 'A aparut o eroare. Te rugam sa incerci din nou.');
}
