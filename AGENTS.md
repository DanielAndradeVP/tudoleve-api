# AGENTS.md

## Cursor Cloud specific instructions

### Project overview

TudoLeve is a headless e-commerce API built with **Laravel 12** (PHP 8.4) using Domain-Driven Design. The backend serves a RESTful API at `/api/v1/...`. See `composer.json` scripts for standard commands (`composer dev`, `composer test`, `composer setup`).

### Services

| Service | Purpose | Required for dev |
|---------|---------|-----------------|
| Laravel API (`php artisan serve`) | Main application server | Yes |
| MySQL 8 | Primary data store | Yes (for app; tests use SQLite in-memory) |
| Redis | Optional cache/queue backend | No (defaults to `database` driver) |
| Vite (`npm run dev`) | Frontend asset compilation (Tailwind/CSS/JS for Blade views) | Only if editing Blade views |
| Queue worker (`php artisan queue:listen`) | Background job processing | Only if testing async features |

### Running the dev server

`composer dev` starts the API server, queue worker, log viewer (Pail), and Vite concurrently. Alternatively, run `php artisan serve` alone for just the API.

### Testing

- **PHPUnit**: `php artisan test` or `composer test`. Tests use SQLite in-memory (`phpunit.xml` overrides `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`), so no MySQL is needed for tests.
- **Linting**: `./vendor/bin/pint --test` (dry-run) or `./vendor/bin/pint` (auto-fix). The existing codebase has pre-existing Pint style issues.

### MySQL setup (local, non-Docker)

MySQL must be running before the app server starts. Start it with:
```bash
sudo mysqld --user=mysql --datadir=/var/lib/mysql --socket=/var/run/mysqld/mysqld.sock --pid-file=/var/run/mysqld/mysqld.pid &
```
The `.env` defaults expect database `tudoleve_api`, user `tudoleve` with empty password on `127.0.0.1:3306`.

### Environment setup

- `.env` is created from `.env.example`; run `php artisan key:generate` if `APP_KEY` is empty.
- Run `php artisan migrate` after DB is available. `php artisan db:seed` populates sample data.

### Cart API caveat

The cart endpoints require either an authenticated user **with a customer relation** or an `X-Cart-Session` header. Create a session via `POST /api/v1/cart/session` and pass it as `X-Cart-Session: <uuid>`.

### Docker

`docker-compose.yml` is available for the full stack (MySQL, Redis, PHP-FPM, Nginx, queue worker, and an external Nuxt frontend). See `README-docker.md`. The frontend repo (`../tudoleve-frontend`) is not part of this repository.
