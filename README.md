# Laravel + Livewire/Volt Starter App

An application scaffolded with Laravel 12, Livewire v3 (Volt + Flux UI), Fortify authentication, Vite, and Tailwind v4. This repo includes convenient Composer scripts for setup and a dev loop, and Pest for testing.

## Overview

- Backend: Laravel Framework 12 (PHP ^8.2)
- Realtime UI: Livewire v3 with Volt single-file components and Flux UI
- Auth: Laravel Fortify
- Admin (optional): Filament 3 — provider is conditionally registered so the app works even if Filament isn’t installed
- Frontend tooling: Vite 7, Tailwind CSS v4
- Testing: Pest v4 with Laravel plugin; in-memory SQLite for tests

Key entry points
- HTTP: `php artisan serve` (Laravel’s built-in server)
- Assets: Vite inputs `resources/css/app.css` and `resources/js/app.js` (see `vite.config.js`)
- Routes: defined in `routes/web.php` (home redirects to `dashboard`; most routes require auth)

## Requirements

- PHP: ^8.2 (8.5 works as well)
- Composer
- Node.js: 20+ recommended
- npm

Database
- Local development: SQLite works well. Create `database/database.sqlite` and use `DB_CONNECTION=sqlite`.
- Tests: in-memory SQLite is used automatically via `phpunit.xml`.

## Getting Started (one command)

The setup script is idempotent and prepares everything (env file, app key, DB, migrations, npm install, and asset build):

```
composer run setup
```

What it does under the hood:
1) `composer install`
2) Create `.env` if missing and `php artisan key:generate`
3) `php artisan migrate --force`
4) `npm install`
5) `npm run build`

## Development

Start the full dev loop (PHP server, queue listener, Pail logs, and Vite dev server) with:

```
composer run dev
```

If you prefer to run things manually:
- PHP server: `php artisan serve`
- Queue (dev): `php artisan queue:listen --tries=1`
- Logs (Pail): `php artisan pail --timeout=0`
- Vite (HMR): `npm run dev`

Common troubleshooting
- Vite manifest error: run `npm run build` or keep `npm run dev` running.
- UI not updating: ensure Vite is running (`npm run dev`) and browser cache is cleared.

## Available Scripts

Composer scripts (`composer.json`):
- `composer run setup` — install deps, create `.env` (if missing), generate key, migrate, install node deps, build assets
- `composer run dev` — concurrently run server, queue, pail logs, and Vite
- `composer run test` — clear config cache and execute the test suite

npm scripts (`package.json`):
- `npm run dev` — start Vite dev server
- `npm run build` — build production assets

## Environment Variables

The setup script creates `.env` from `.env.example` if needed. Review `.env.example` and set variables for your environment. Notable variables used in this repo and tests:

- `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`
- `MAIL_MAILER`

Tests use these overrides via `phpunit.xml`:
- `DB_CONNECTION=sqlite`
- `DB_DATABASE=:memory:`
- `CACHE_STORE=array`, `SESSION_DRIVER=array`, `MAIL_MAILER=array`, `QUEUE_CONNECTION=sync`

TODO
- Document any additional service integrations and their env vars if/when added (e.g., third-party APIs, storage, mail drivers).

## Testing

This project uses Pest v4.

- Run all tests: `php artisan test`
- Run a single file: `php artisan test tests/Feature/ExampleTest.php`
- Filter by name: `php artisan test --filter="returns_a_successful_response"`
- Stop on first failure: `php artisan test -f`

Notes
- Tests default to in-memory SQLite and array drivers for cache/session/mail (see `phpunit.xml`).
- Prefer model factories and states for test data.

## Project Structure

High-level layout:

```
app/                     # Application code (models, providers, etc.)
bootstrap/providers.php  # Provider registration (Filament provider is conditional)
config/                  # Config files
database/                # Migrations, factories, seeders
public/                  # Public web root (index.php)
resources/               # Blade views, Volt components, CSS/JS
routes/web.php           # Web routes (auth-protected dashboard and settings)
tests/                   # Pest tests (Feature, Unit)
vite.config.js           # Vite configuration (inputs: css/app.css, js/app.js)
composer.json            # PHP deps and Composer scripts
package.json             # Frontend deps and npm scripts
phpunit.xml              # Test env configuration
```

## Providers & Auth

- Providers are registered in `bootstrap/providers.php` (Laravel 12 style).
- Filament admin provider is only added if `Filament\PanelProvider` exists.
- Fortify is installed and registered for authentication flows.
- Livewire Volt is enabled; use the `@volt` directive for single-file components.

## Code Style

- Use Laravel Pint: `vendor/bin/pint --dirty` to format changed PHP files before committing.

## CI

TODO
- Add a CI workflow (e.g., GitHub Actions) to run `composer run test` and `npm run build`.

## License

MIT License (see `composer.json`).

TODO
- Add a `LICENSE` file with the MIT text if distributing publicly.
