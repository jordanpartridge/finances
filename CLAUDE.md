# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application for managing financial portfolios and positions. The application uses Pest for testing, Pint for code formatting, and Larastan for static analysis.

## Core Domain Models

### Portfolio-Position Architecture
- `Portfolio` model: Represents investment portfolios with name, description, and value
- `Position` model: Represents individual positions with ticker and shares
- Currently models are **not** related - there's no foreign key relationship between Portfolio and Position tables
- If you add relationships in the future, remember to update migrations and models accordingly

## Development Commands

### Setup & Installation
```bash
composer setup  # Full setup: install deps, copy .env, generate key, migrate DB, install/build npm
```

### Running the Application
```bash
composer dev  # Starts all services concurrently: server, queue, logs (pail), and vite
# Individual services:
php artisan serve
php artisan queue:listen --tries=1
php artisan pail --timeout=0
npm run dev
```

### Testing
```bash
composer test              # Run full test suite with Pest
php artisan test           # Same as composer test
php artisan test --filter=PortfolioTest  # Run specific test file
php artisan test --filter=test_example   # Run specific test method
```

### Code Quality
```bash
./vendor/bin/pint          # Format code (Laravel Pint)
./vendor/bin/phpstan       # Run static analysis (Larastan level 3.7)
```

### Database
```bash
php artisan migrate        # Run migrations
php artisan migrate:fresh  # Drop all tables and re-run migrations
php artisan migrate:fresh --seed  # Fresh migration + seed data
```

### Frontend
```bash
npm run build              # Production build with Vite
npm run dev                # Development server with hot reload
```

## Testing Architecture

- Test framework: **Pest 4.1** (not PHPUnit syntax)
- Test database: SQLite in-memory (`:memory:`)
- Feature tests use `RefreshDatabase` trait automatically (configured in `tests/Pest.php`)
- Unit tests do NOT use `RefreshDatabase` by default
- Tests located in `tests/Feature/` and `tests/Unit/`

### Pest Configuration Notes
- All Feature tests automatically include `TestCase` and `RefreshDatabase`
- Custom expectation `toBeOne()` is defined in `tests/Pest.php`
- Use Pest syntax (`it()`, `test()`, `expect()`) not PHPUnit syntax

## Code Style Conventions

### Strict Types
- Non-User models use `declare(strict_types=1);` at the top (see `Portfolio.php`)
- User model does NOT have strict types (Laravel default)

### PHPDoc Standards
- Use generic type annotations: `@use HasFactory<\Database\Factories\UserFactory>`
- Document return types: `@return array<string, string>`
- Document array types: `@var list<string>` for simple arrays

### Casts Method Pattern
- Laravel 12 uses `casts()` method, not `$casts` property
- Return array from `protected function casts(): array`

## Architecture Notes

- Controllers are in `app/Http/Controllers/`
- Models are in `app/Models/`
- Routes defined in `routes/web.php` (currently minimal - just welcome page)
- Frontend uses Tailwind CSS v4 with Vite
- No API routes configured yet - add `routes/api.php` if needed

## Migration Naming
- Standard Laravel timestamp format: `YYYY_MM_DD_HHMMSS_description.php`
- Custom migrations: portfolios (2025_10_27), positions (2025_10_29)

## Environment Configuration

- Database: SQLite by default (check `.env` for `DB_CONNECTION=sqlite`)
- Testing always uses in-memory SQLite regardless of `.env`
- Queue connection defaults may vary - check `.env.example`
