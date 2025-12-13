# Procurement Management System (Laravel 12, PHP 8.3, SQL Server)

This repository contains an incomplete but functional procurement and purchase order management system built on Laravel 12, using PHP 8.3 and SQL Server (`sqlsrv`) as the primary database. Frontend assets are managed with Vite, and jQuery/Bootstrap are used for UI.

The project is designed to run with Laravel Herd (PHP 8.3), but it also works with a standard PHP installation on Windows/macOS/Linux. SQL Server compatibility is implemented throughout migrations and queries.

## 1) Project Overview
- Current state of development
  - Session-based authentication using legacy `login` table and minimal user payload (`app/Domain/Auth/AuthenticateUserAction.php`).
  - Role-based dashboards with requestor and superadmin views (`app/Http/Controllers/DashboardController.php:40`).
  - Superadmin control panel with system tabs and APIs (branding, logs, users, database info/actions) (`app/Http/Controllers/SuperAdminController.php:216`).
  - Dynamic branding CSS powered by settings data (`app/Services/BrandingService.php:191`, served via `BrandingController@css`).
  - Basic items, suppliers, and purchase order pages and APIs.
  - Vite bundling configured with multiple JS entry points (`vite.config.js:7`).
- Key implemented features
  - Authentication and session management (`app/Http/Controllers/AuthController.php:30`, `AuthenticateUserAction`).
  - Requestor dashboard metrics and PO listing with caching (`DashboardController@index` and `summary`).
  - Superadmin dashboards: branding, status management, security, logs, database info, backups (view pages in `resources/views/superadmin/tabs`, routes in `routes/web.php:62`).
  - Dynamic CSS for branding and status (`BrandingController@css`, `SettingsController@dynamicStatusCss`).
  - SQL Server-oriented migrations with GUID primary keys and `GETDATE()` defaults.
  - Deployment helper scripts (`deploy-production.bat`, `deploy-production.sh`) and nginx config (`nginx-procurement.conf`).
- Known limitations and missing components
  - Authentication is minimal and session-based; no full Laravel auth scaffolding or robust authorization middleware on all routes.
  - Several Settings endpoints are stubs (`SettingsController` has `abort(404)` for logo upload/preferences/notifications).
  - Testing is minimal; Composer does not include PHPUnit by default. Tests likely require setup.
  - Some superadmin routes are explicitly marked as unrestricted in comments and rely on session role checks rather than middleware.
  - Warmup script referenced in deployment scripts (`warmup-system.php`) is not present.
  - Frontend uses CDNs for Bootstrap/Font Awesome and jQuery; this may not be suitable for locked-down networks.

## 2) Development Environment Setup
- Prerequisites
  - PHP 8.3 (Herd or standard PHP)
  - Composer
  - Node.js 18+ and npm (for Vite)
  - SQL Server (local or remote instance)
  - ODBC Driver for SQL Server (recommended: Driver 18)
  - PHP extensions: `pdo_sqlsrv`, `sqlsrv`
- PHP 8.3 with Herd
  - Install Herd and ensure PHP 8.3 is enabled.
  - Enable `pdo_sqlsrv` and `sqlsrv` for PHP 8.3 via Herd’s extension manager if available; otherwise enable manually in `php.ini`.
  - Verify extensions: run `php -m` and confirm `pdo_sqlsrv` and `sqlsrv` are listed.
- PHP 8.3 without Herd (Windows)
  - Install Microsoft ODBC Driver for SQL Server (Driver 18).
  - Download SQLSRV drivers matching PHP 8.3 (VC runtime and thread-safety must match your PHP build).
  - Enable in `php.ini`:  
    ```
    extension=sqlsrv
    extension=pdo_sqlsrv
    ```
  - Verify with `php -m`.
- Project setup
  - Copy `.env.example` to `.env` and configure environment variables (see below).
  - Install PHP dependencies: `composer install`
  - Install Node dependencies: `npm install`
  - Generate app key: `php artisan key:generate`
  - Run migrations: `php artisan migrate`
  - Seed baseline data: `php artisan db:seed`
- Environment variables (minimum)
  ```
  APP_NAME="Procurement System"
  APP_ENV=local
  APP_KEY=base64:generated-by-artisan
  APP_DEBUG=true
  APP_URL=http://localhost
  
  DB_CONNECTION=sqlsrv
  DB_HOST=localhost
  DB_PORT=1433
  DB_DATABASE=ProcurementDb
  DB_USERNAME=sa
  DB_PASSWORD=YourStrong!Passw0rd
  DB_ENCRYPT=no
  DB_TRUST_SERVER_CERTIFICATE=true
  
  CACHE_DRIVER=file
  SESSION_DRIVER=file
  QUEUE_CONNECTION=sync
  ```
- Start development servers
  - Using Composer script (concurrently starts PHP server, queue, and Vite dev):  
    `composer run dev`
  - Or separately:  
    `php artisan serve`  
    `npm run dev`

## 3) Database Configuration
- Driver and connection
  - Laravel connection: set `DB_CONNECTION=sqlsrv` with host/port/database/username/password in `.env`.
  - Internally, the project uses SQL Server-specific functions in migrations (e.g., `NEWSEQUENTIALID()`, `GETDATE()`).
- Schema overview (key tables)
  - `users`: core user table with GUID PK (`database/migrations/0001_01_01_000100_create_users_table.php:12`)
  - `login`: legacy login table for credentials (`0001_01_01_000110_create_login_table.php:12`)
  - `role_types` / `roles`: user role mapping (`0001_01_01_000170_create_role_types_table.php:12`, `0001_01_01_000180_create_roles_table.php:12`)
  - `suppliers`: supplier master (`0001_01_01_000120_create_suppliers_table.php:12`)
  - `purchase_orders`: PO header (`0001_01_01_000130_create_purchase_orders_table.php:12`)
  - `items`: PO line items (`0001_01_01_000160_create_items_table.php:12`)
  - `statuses`: workflow status definitions (includes `color`, `sort_order`) (`0001_01_01_000140_create_statuses_table.php:12`)
  - `system_settings`: configurable constants/settings (`2025_01_23_000004_create_system_settings_table.php:12`)
- Connection string format (PDO `sqlsrv`)
  - Laravel config builds DSN internally from `.env`. For manual tests:  
    `sqlsrv:Server=<HOST>,<PORT>;Database=<DBNAME>`
  - Encryption/trust options are mapped via PDO SQLSRV attributes when testing connections (`SuperAdminController@updateDatabaseSettings`).
- Sample data
  - Run `php artisan db:seed` to populate:
    - Role types, statuses, baseline users/logins/roles, suppliers, sample POs/items, constants/settings (`database/seeders/DatabaseSeeder.php:15`).

## 4) Project Structure
- Directory layout
  - `app/Http/Controllers`: web/API controllers (auth, dashboards, POs, suppliers, superadmin).
  - `app/Domain/Auth`: authentication action using legacy tables.
  - `app/Services`: branding, constants, status management, system monitoring.
  - `app/Models`: Eloquent models for system settings, security logs, sessions.
  - `app/Http/Middleware`: API authentication middleware (session-based).
  - `database/migrations`: SQL Server-aware migrations.
  - `database/seeders`: seed data for roles, statuses, users, POs, items, settings.
  - `resources/views`: Blade templates for dashboards, superadmin tabs, items, suppliers, settings.
  - `resources/js` and `resources/css`: Vite-managed assets (components, pages, dashboards).
  - `routes/web.php`: HTTP routes; note the superadmin group and API endpoints.
  - `vite.config.js`: Vite entry points and plugin configuration.
  - `deploy-production.*`: helper scripts for production deployment.
  - `nginx-procurement.conf`: sample Nginx config with timeout tuning.
- Core components
  - Layout and Vite asset loading (`resources/views/layouts/app.blade.php:13`).
  - Requestor dashboard (`resources/views/dashboards/requestor.blade.php`) and `resources/js/dashboards/requestor-dashboard.js`.
  - Superadmin dashboard with tabs (`resources/views/superadmin/tabs/*`) and controllers under `SuperAdminController`.
  - Branding dynamic CSS (`BrandingService@generateCSS` served by `BrandingController@css`).
- Architectural decisions
  - Roles sourced from legacy tables; session payload drives role-based rendering (`AuthenticateUserAction`).
  - SQL Server first: GUID PKs, default timestamps via `GETDATE()`, careful FK order for imports/exports.
  - Settings/constants read from `system_settings` with caching (`ConstantsService`), falling back to `.env` and `config/constants.php`.
  - Client assets via Vite; Blade uses `@vite(...)` for required bundles and dynamic CSS routes.

## 5) Development Guidelines
- Coding standards
  - Follow Laravel/PSR-12 conventions.
  - Keep controllers thin; prefer `Services` for reusable logic.
  - Use `DB`/query builder for SQL Server queries where Eloquent is not ideal.
- Linting and formatting
  - Pint is included: run `vendor/bin/pint` to format PHP code.
- Testing procedures
  - Minimal tests exist; ensure PHPUnit is available if adding tests.
  - Recommended: add feature tests for superadmin APIs, dashboard metrics, and purchase order workflows.
  - Commands:
    - `php artisan test` (if `phpunit/phpunit` is installed)
    - Or `vendor/bin/phpunit`
- Contribution workflow
  - Create feature branches, open PRs, and ensure Vite build passes.
  - Add clear commit messages and update this README when adding major features.
  - Consider adding middleware protections before exposing new endpoints.
- Deployment process
  - Windows: `deploy-production.bat`
  - Linux/macOS: `deploy-production.sh`
  - Steps include: Composer install (`--no-dev`), permissions, optional warmup, `npm run build`.
  - Configure web server; sample Nginx config provided in `nginx-procurement.conf`.

## 6) Future Development Roadmap
- Planned features
  - Full authentication & authorization with middleware guards per route.
  - Complete settings UX (logo upload, preferences, notifications).
  - Requestor dashboard interactions and live updates.
  - Enhanced PO workflows, printing, and audit logging.
  - Role-based permissions admin UI.
- Technical debt
  - Replace direct DB calls with dedicated repository/services where appropriate.
  - Normalize use of GUIDs and timestamp defaults across all tables.
  - Consolidate constants/settings and improve caching invalidation.
  - Add robust test suite and CI.
- Suggested improvements
  - Move CDN dependencies to local bundles for better control.
  - Add environment-specific configs for Vite/Bootstrap/Font Awesome.
  - Harden superadmin APIs with proper middleware and rate limiting.
  - Provide real backup/export tooling specific to SQL Server dumps.

## SQL Server + PHP 8.3 Troubleshooting
- “could not find driver” when connecting
  - Confirm `pdo_sqlsrv` and `sqlsrv` are enabled in `php.ini`.
  - Confirm ODBC Driver 18 is installed and accessible.
- Timeouts on first page load (504)
  - Use provided Nginx config timeouts and run deployment scripts to warm caches.
- Vite “entry module cannot be resolved”
  - Verify file exists and matches path/case in `vite.config.js`.
  - Clear caches: delete `node_modules` and `public/build`, then `npm install` and `npm run build`.
  - Ensure `@vite([...])` references in Blade match configured inputs.
- Switching to SQL Server from default sqlite
  - Set `DB_CONNECTION=sqlsrv` and ensure all DB env vars are correct.
  - Run `php artisan config:clear` and `php artisan migrate`.

## Quick References
- Controllers
  - `app/Http/Controllers/DashboardController.php:40` role routing and metrics
  - `app/Http/Controllers/SuperAdminController.php:216` superadmin index and APIs
  - `app/Http/Controllers/BrandingController.php:19` dynamic CSS response
  - `app/Http/Controllers/AuthController.php:30` login handling
- Services
  - `app/Services/BrandingService.php:191` dynamic CSS generation
  - `app/Services/ConstantsService.php:17` config hierarchy and caching
- Routes
  - `routes/web.php:31` dashboards
  - `routes/web.php:62` superadmin APIs (branding, logs, DB)
- Assets
  - `vite.config.js:7` Vite inputs
  - `resources/views/layouts/app.blade.php:13` `@vite` usage

Keep this README updated as you add or complete features. When changing database schema or configuration, update the relevant sections and provide migration notes for future developers.

