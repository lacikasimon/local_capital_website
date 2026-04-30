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
                $slug = save_post($site, $admin, $match[1]);
                redirect('/admin/posts/' . $slug . '?lang=' . $adminLang . '&ok=1');
            }

            if (preg_match('#^/admin/messages/([0-9]+)$#', $path, $match)) {
                update_contact_message_status((int) $match[1]);
                redirect('/admin/messages?lang=' . $adminLang . '&ok=1');
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

        if ($path === '/admin/messages') {
            echo render_contact_messages($site, $admin);
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
        echo render_error_page(error_page_text($adminLang, 'not_found_title'), error_page_text($adminLang, 'not_found_message'), $adminLang);
        exit;
    }

    $site = load_site($language);

    if ((route_page_key_for_path($language, $path) === 'contact') && $method === 'POST') {
        $result = save_contact_message($language, $_POST);
        if ($result['ok']) {
            redirect(localized_path('/contact', $language) . '?sent=1');
        }

        http_response_code(422);
        echo render_contact($site, $result['errors'], $result['old']);
        exit;
    }

    if ($method !== 'GET') {
        http_response_code(405);
        echo render_error_page(error_page_text($language, 'method_title'), error_page_text($language, 'method_message'), $language);
        exit;
    }

    if ($path === '/robots.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        echo render_robots_txt();
        exit;
    }

    if ($path === '/llms.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        echo render_llms_txt();
        exit;
    }

    if ($path === '/sitemap.xml') {
        header('Content-Type: application/xml; charset=utf-8');
        echo render_sitemap_xml();
        exit;
    }

    if (blog_index_path_matches($language, $path)) {
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

    if (case_study_archive_path_matches($language, $path)) {
        echo render_case_study_archive($site, 'business');
        exit;
    }

    [$key, $page] = page_by_path($site, $path);
    if (!$page) {
        http_response_code(404);
        echo render_error_page(error_page_text($language, 'not_found_title'), error_page_text($language, 'not_found_message'), $language);
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
    echo render_error_page(error_page_text($language ?? DEFAULT_LANGUAGE, 'server_title'), $debug ? $error->getMessage() : error_page_text($language ?? DEFAULT_LANGUAGE, 'server_message'), $language ?? DEFAULT_LANGUAGE);
}
