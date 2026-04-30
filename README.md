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
- `scripts/import-wordpress-content.js` regenerates the WordPress import SQL from the current public site.
- `public/downloads/` stores downloaded PDF documents linked from the old site.
- `scripts/create-admin.php` creates or updates admin users.
- Root `.htaccess` protects private folders if the whole project is uploaded to a shared hosting document root.

## Install

1. Create a MySQL database and user.
2. Import `database/schema.sql`, then `database/imported-wordpress.sql` in phpMyAdmin or MySQL CLI. You can also run `php scripts/install.php` after configuring the database.
3. Copy the config file:

```sh
cp config/config.example.php config/config.php
```

4. Edit `config/config.php` with the database credentials.
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

## Security notes

- No WordPress, plugins, XML-RPC, Elementor, or public CMS REST surface.
- Admin passwords use PHP `password_hash`.
- Admin sessions use HttpOnly cookies.
- Admin writes use CSRF tokens.
- Security headers are sent by PHP and partially mirrored in `public/.htaccess`.
- Keep `config/config.php` outside git and back up the MySQL database.
