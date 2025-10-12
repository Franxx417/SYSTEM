# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Development Commands

### Project Setup
```bash
# Initial setup
composer install --no-interaction --prefer-dist
npm ci

# Environment setup
cp .env.example .env
php artisan key:generate
```

### Development Server
```bash
# Full development stack with concurrent processes (Laravel + Queue + Vite)
composer dev

# Alternative manual setup
php artisan serve --host=127.0.0.1 --port=3000
php artisan queue:listen --tries=1  # In separate terminal
npm run dev                         # In separate terminal
```

### Database Operations
```bash
# Fresh migration and seeding (destructive)
php artisan migrate:fresh --seed --ansi

# Safe migration (preserves existing tables)
php artisan migrate --ansi
php artisan db:seed --ansi
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Clear configuration cache before testing
php artisan config:clear --ansi
```

### Asset Building
```bash
# Development build with hot reload
npm run dev

# Production build
npm run build
```

### Maintenance Commands
```bash
# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list | cat

# Interactive shell
php artisan tinker
```

## Architecture Overview

### Core System
This is a **Laravel 12 procurement workflow system** with SQL Server backend and Vite-powered frontend assets. The system manages purchase orders with role-based approvals.

### Role Structure (Simplified 2-Role System)
The system has been **consolidated from 5 roles to 2 roles** as of October 2025:

- **`requestor`**: Creates and manages purchase orders, views suppliers
- **`superadmin`**: Complete system administration with full access to all features

### Directory Structure

#### Backend (Laravel)
- **`app/Http/Controllers/`**: Main controllers
  - `SuperAdminController` - Complete system administration
  - `PurchaseOrderController` - PO creation and management  
  - `SupplierController` - Supplier management (requestor + superadmin only)
  - `AuthController` - Custom authentication using `login` table
- **`app/Services/`**: Business logic services
- **`database/seeders/`**: Database seeding with test accounts and sample data

#### Frontend (Vite + Laravel)
- **`resources/views/superadmin/`**: All superadmin views consolidated here
- **`resources/views/dashboards/requestor.blade.php`**: Requestor dashboard
- **`resources/js/`**: Organized JavaScript modules:
  - `components/` - Reusable components (modal-manager, status-sync)
  - `dashboards/` - Role-specific dashboard logic
  - `pages/` - Page-specific functionality (PO creation, editing, etc.)

#### Key Configuration
- **`vite.config.js`**: Comprehensive asset pipeline with 40+ entry points
- **SQL Server PDO Connection**: Custom connection handling in `SqlServerPDOConnection.php`

### Authentication System
- Uses custom `login` table (not Laravel's default users table)
- Session-based authentication with role information stored in session
- Test accounts available (see README.md for credentials)

### Database
- **Microsoft SQL Server** backend with SQL Server PDO drivers
- Migrations are idempotent (safe to run on existing schema)
- Comprehensive seeding includes roles, statuses, users, suppliers, and sample POs

## Development Notes

### Working with Purchase Orders
- PO numbers auto-generated as `YYYYMMDD-XXX` format
- Real-time calculation of totals (shipping, discount, VAT)
- Unit prices auto-fill from previous orders for same supplier/item
- Items can be added from dropdown (previous items) or manually entered

### SQL Server Configuration
- Requires `pdo_sqlsrv` and `sqlsrv` PHP extensions
- Connection configuration in `.env` with `DB_CONNECTION=sqlsrv`
- Custom connection handling for robust SQL Server integration

### Asset Pipeline
- Vite handles both CSS and JavaScript compilation
- TailwindCSS 4.0 integration
- Bootstrap 5.3.7 via Vite
- Page-specific JavaScript modules loaded conditionally

### Development Server URLs
- Laravel app: `http://127.0.0.1:3000` 
- Vite dev server: `http://127.0.0.1:5173`
- Login page: `http://127.0.0.1:3000/login`

## Project-Specific Context

### Recent Major Changes
- **October 2025**: Role consolidation from 5 roles to 2 roles
- Removed `authorized_personnel`, `finance_controller`, `department_head` roles
- Consolidated all admin functionality into `superadmin` role
- View structure reorganized with `superadmin/` directory

### Legacy Components
- Some files marked as "legacy" in vite.config.js (e.g., `po-form.js`)
- May contain references to old 5-role system in comments or unused code
- Database may still contain unused role types (safe to ignore)

### Troubleshooting
- **419 CSRF errors**: Ensure `APP_URL` and `SESSION_DOMAIN` match in `.env`
- **SQL Server connection**: Verify ODBC drivers and PHP extensions enabled
- **Asset compilation**: Run `npm run build` for production or `npm run dev` for development

### Key Files to Understand
- `SuperAdminController.php` - Central admin functionality
- `resources/views/superadmin/dashboard.blade.php` - Main admin interface
- `PurchaseOrderController.php` - Core business logic
- `resources/js/pages/po-create.js` - PO creation frontend logic
