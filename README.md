# Local Capital website

Apache + PHP + MySQL mini-CMS for replacing the current WordPress site.

## Server requirements

- Apache with `mod_rewrite`
- PHP 8.1+ with `pdo_mysql`
- MySQL or MariaDB with `utf8mb4`
- HTTPS enabled in production

## Directory layout

- `public/` is the Apache document root.
- `app/` contains PHP application code and must not be public.
- `config/config.php` contains database credentials and is ignored by git.
- `database/schema.sql` creates tables and seed content.
- `database/imported-wordpress.sql` contains the crawled WordPress pages, services, case studies, and link inventory.
- `database/content-overrides.sql` cleans imported pages, services, and case studies into structured, non-repeating sections based on the original WordPress content.
- `database/ifn-trust-content.sql` applies the IFN trust, responsible-lending, legal-identification, and multilingual guide content layer.
- `database/multilingual-content-fixes.sql` normalizes localized routes and replaces remaining imported Romanian legal bodies in English/Hungarian content.
- `database/anaf-consent.sql` creates the encrypted ANAF consent tables used by the Romanian consent form and admin PDF workflow.
- `scripts/import-wordpress-content.js` regenerates the WordPress import SQL from the current public site.
- `public/downloads/` stores downloaded PDF documents linked from the old site.
- `scripts/create-admin.php` creates or updates admin users.
- Root `.htaccess` protects private folders if the whole project is uploaded to a shared hosting document root.
- Contact form submissions are stored in the `contact_messages` table and can be reviewed from the admin area.

## Install

1. Create a MySQL database and user.
2. Import `database/schema.sql`, then `database/imported-wordpress.sql`, then `database/content-overrides.sql`, then `database/ifn-trust-content.sql`, then `database/multilingual-content-fixes.sql`, then `database/anaf-consent.sql` in phpMyAdmin or MySQL CLI. You can also run `php scripts/install.php` after configuring the database.
3. Copy the config file:

```sh
cp config/config.example.php config/config.php
```

4. Edit `config/config.php` with the database credentials.
   Also set a long random `app.form_secret`; it signs public contact form tokens and IP hashes.
   To enable Google reCAPTCHA v3, add the site key and secret key in the `recaptcha` config block and set `enabled` to `true`.
   Content SQL updates are applied automatically on the first web request after
   upload, including on cPanel/shared hosting. The app tracks the checksums in
   the `content_update_runs` table and skips unchanged files. Set
   `app.auto_apply_content_updates` to `false` if you want to run updates only
   manually with `php scripts/apply-content-updates.php`.
5. Point Apache document root to the `public/` directory when the hosting panel allows it. If not, upload the whole project and keep the root `.htaccess` file.
6. Create the admin user:

```sh
ADMIN_PASSWORD='choose-a-long-password' php scripts/create-admin.php admin
```

Then open `/admin`.

## Local Docker testing

Docker is only for local testing. Production on cPanel should use native Apache/PHP/MySQL with `config/config.php`.

Start the local stack:

```sh
docker compose up --build
```

On every app container start, Docker automatically applies changed content SQL
layers (`content-overrides.sql`, `ifn-trust-content.sql`,
`multilingual-content-fixes.sql`, and `anaf-consent.sql`) to the existing local
database. Each file is tracked by checksum, so unchanged files are skipped on
later restarts. Set `LOCALCAPITAL_AUTO_APPLY_CONTENT=0` to disable this, or
`LOCALCAPITAL_FORCE_CONTENT_UPDATES=1` to reapply the tracked files.

Open:

- Website: `http://localhost:8080`
- Admin: `http://localhost:8080/admin`
- phpMyAdmin: `http://localhost:8081`

The Docker MySQL credentials are intentionally local-only:

```txt
host: mysql
database: localcapital
user: localcapital
password: localcapital
root password: localcapital_root
```

Create or update a local admin user:

```sh
docker compose exec app sh -lc "ADMIN_PASSWORD='choose-a-long-password' php scripts/create-admin.php admin"
```

Repair text that was imported through a wrong MySQL client charset:

```sh
docker compose exec app php scripts/repair-encoding.php
```

If you change `database/schema.sql` and want a clean database import:

```sh
docker compose down -v
docker compose up --build
```

Regenerate the WordPress content import:

```sh
node scripts/import-wordpress-content.js
```

The importer currently pulls:

- WordPress REST pages for `ro`, `en`, and `hu`
- WordPress REST posts for `ro`, `en`, and `hu`
- `finlon_service` entries
- sitemap case study URLs
- internal and external links found on the crawled pages

The source site currently exposes the same page set through the language REST filters, while `/ro/` and `/hu/` return sparse WordPress archive/not-found pages. The importer still creates language rows so the new CMS can be edited per language from `/admin?lang=ro`, `/admin?lang=en`, and `/admin?lang=hu`.

Imported links can be reviewed from `/admin/links?lang=ro`, `/admin/links?lang=en`, and `/admin/links?lang=hu`.
Contact messages can be reviewed from `/admin/messages?lang=ro`, `/admin/messages?lang=en`, and `/admin/messages?lang=hu`.

## SEO and AI discovery

- `/sitemap.xml` exposes all public language variants with `hreflang` alternates.
- `/robots.txt` points crawlers to the sitemap and allows public AI crawler access while blocking admin and source folders.
- `/llms.txt` provides an AI-readable public summary of the company, languages, key pages, services, articles, and policy pages.
- Public pages include canonical URLs, Open Graph, Twitter card metadata, `ai-summary`, and Schema.org JSON-LD `@graph` data.
- Page FAQ content stored in `extra_json` is rendered visibly and emitted as `FAQPage` structured data.

## Security notes

- No WordPress, plugins, XML-RPC, Elementor, or public CMS REST surface.
- Admin passwords use PHP `password_hash`.
- Admin sessions use HttpOnly cookies.
- Admin writes use CSRF tokens.
- Admin login uses IP-hash failure tracking, temporary application-level bans, and fail2ban-friendly PHP error log lines.
- The public contact form uses a signed time-limited token, honeypot field, IP-hash rate limiting, and optional Google reCAPTCHA v3 verification before storing messages.
- Google reCAPTCHA v3 can be enabled site-wide through `config/config.php`:

```php
'recaptcha' => [
    'enabled' => true,
    'site_key' => 'GOOGLE_RECAPTCHA_V3_SITE_KEY',
    'secret_key' => 'GOOGLE_RECAPTCHA_V3_SECRET_KEY',
    'min_score' => 0.5,
    'actions' => [
        'page_view' => 0.0,
        'contact' => 0.5,
        'anaf_consent' => 0.6,
        'admin_login' => 0.7,
    ],
],
```

- The Romanian ANAF agreement form at `/acord-anaf` uses the same signed token, honeypot, rate limiting, optional reCAPTCHA v3, encrypted storage, one-time public links, and admin-only PDF generation. A local smoke/security check is available with `php scripts/security-check-anaf.php`.

- Admin login ban thresholds can be tuned in `config/config.php`:

```php
'security' => [
    'admin_login_max_failures' => 5,
    'admin_login_window_minutes' => 15,
    'admin_login_ban_minutes' => 30,
],
```

- On cPanel shared hosting, the application-level ban works without server access. On a VPS/root server, fail2ban can also watch PHP error logs with a filter matching `LOCALCAPITAL_FAIL2BAN event=admin-login-failure ip=<HOST>`.
- Security headers are sent by PHP and partially mirrored in `public/.htaccess`.
- Keep `config/config.php` outside git and back up the MySQL database.
