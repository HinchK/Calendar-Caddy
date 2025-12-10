# Project Development Guidelines (Advanced)

This document captures project-specific knowledge to speed up setup, testing, and day-to-day development for this Laravel 12 + Livewire/Volt app.

## 1) Build & Configuration

- PHP / Node
  - PHP 8.2+ (composer.json requires ^8.2). Local env used during authoring: 8.5 OK.
  - Node 20+ recommended; Vite is used for asset bundling.

- Initial setup (idempotent)
  - Use the provided Composer script which handles env, key, DB, migrations, npm install, and build:
    - `composer run setup`
  - If you need to run steps manually:
    - `composer install`
    - `cp .env.example .env` (if missing)
    - `php artisan key:generate`
    - Database: default tests run on in-memory sqlite; for local dev set `DB_CONNECTION=sqlite` and point to `database/database.sqlite` or your preferred DB.
    - `php artisan migrate --force`
    - `npm install`
    - `npm run build` (or `npm run dev` for watch mode)

- Dev convenience script
  - `composer run dev` spawns: PHP server, queue listener, Pail logs, and Vite dev server concurrently.
  - If UI changes don’t reflect, run: `npm run dev` (for HMR) or `npm run build`.

- Providers registration (Laravel 12 style)
  - Providers live in `bootstrap/providers.php`.
  - This project conditionally registers the Filament Admin Panel provider to prevent failures when Filament is not installed in certain environments:
    - In `bootstrap/providers.php`, `App\Providers\Filament\AdminPanelProvider` is registered only if `Filament\PanelProvider` exists.

## 2) Testing

- Framework
  - Pest v4 with Laravel plugin is installed. PHPUnit config is in `phpunit.xml`. By default, tests run with in-memory sqlite and array drivers for cache/session/mail.
  - Feature and Unit test directories are standard: `tests/Feature`, `tests/Unit`.

- Running tests (minimal first)
  - All tests: `php artisan test`
  - Single file: `php artisan test tests/Feature/ExampleTest.php`
  - Name filter: `php artisan test --filter="returns_a_successful_response"`
  - Stop on failure (useful when iterating): `php artisan test -f`

- Creating tests
  - Feature test (Pest): `php artisan make:test --pest ExampleFeatureTest`
  - Unit test (Pest): `php artisan make:test --pest --unit MathTest`
  - Livewire/Volt tests: use Pest + `Livewire\Volt\Volt::test()` as in the Volt examples; prefer factories when models are involved.

- Database & migrations in tests
  - PhpUnit env sets `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`; migrations are typically auto-discovered and run per test case via traits used in tests (e.g., `RefreshDatabase`).
  - If a test requires seeded data, use factories and states; avoid manual inserts.

- Example: Verified test run (performed while writing this doc)
  - A temporary Pest test was added and executed to ensure the flow works in this repo:
    - Command run: `php artisan test tests/Feature/GuidelinesSmokeTest.php`
    - Result: passed (1 assertion). The temporary file was removed after verification.

## 3) Additional Development Notes

- Code style
  - Use Laravel Pint. Before committing, format changed PHP files:
    - `vendor/bin/pint --dirty`
  - Follow existing code style and naming (see `app/` for conventions). Prefer PHP 8 features (constructor property promotion, strict types in tests, explicit return types).

- Laravel 12 specifics in this repo
  - No `app/Http/Middleware` or `Console\Kernel.php` by default; middleware and routing are wired via `bootstrap/app.php`. Providers list is in `bootstrap/providers.php`.
  - Commands in `app/Console/Commands/` auto-register.

- Authentication
  - Fortify is installed and registered. If you alter auth flows, prefer Fortify customizations and policies over ad-hoc solutions.

- Livewire / Volt / Flux UI
  - Use Livewire v3 patterns (`App\Livewire` namespace, `wire:model.live` for real-time updates).
  - Volt is available for page/component interactivity. Place Volt single-file components under `resources/views` using the `@volt` directive.
  - Prefer Flux UI Free components where suitable; check available components list and existing usage before building new Blade components.

- Frontend
  - Tailwind v4 (CSS-first config); import via `@import "tailwindcss";`. Do not use deprecated v3 utilities.

- Performance
  - Use Eloquent relationships with eager loading to avoid N+1. Use API Resources for APIs where applicable.

- Debugging
  - Use `php artisan tinker` for quick Eloquent checks. Use Laravel Boost tools when available for browser logs and doc searches.

- CI
  - See `.github/workflows/tests.yml` if present for CI specifics. Keep tests deterministic and fast; prefer targeted runs locally.

## 4) Common Gotchas in This Repo

- Filament dependency during testing
  - Tests may fail early if Filament is referenced when not installed. This project guards Filament provider registration (see Build section). If you add other Filament-dependent providers, apply the same `class_exists` guard pattern or ensure Filament is present in the environment.

- Route names in tests
  - Some tests reference named routes like `route('home')`. Ensure these exist or adjust tests accordingly when altering routing files.

- Vite asset errors
  - If you hit “Unable to locate file in Vite manifest”, run `npm run build` or start `npm run dev`.

## 5) Quick Commands Reference

- Install & build: `composer run setup`
- Dev loop: `composer run dev`
- Tests — all: `php artisan test`
- Tests — file: `php artisan test tests/Feature/ExampleTest.php`
- Pint format: `vendor/bin/pint --dirty`
