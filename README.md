## Procurement System (Laravel 12 + SQL Server)

This is a role‑based procurement workflow with online approvals built on Laravel 12 and SQL Server. It includes login, role‑specific dashboards, purchase order creation with items, supplier management, and approval actions. The system supports two role types: **requestor** (for creating and managing purchase orders) and **superadmin** (for system administration and approvals).

### Prerequisites
- PHP 8.2+ (with `ext-fileinfo`, `ext-mbstring`)
- Composer 2.x
- Node.js 18+ (recommended 20+) and npm
- Microsoft SQL Server (Express is fine)
- Microsoft ODBC Driver for SQL Server (17 or 18)
- Microsoft PHP Drivers for SQL Server (`pdo_sqlsrv`, `sqlsrv`) enabled in `php.ini`

### 1) Clone and install
```
git clone <your-repo-url> pdo
cd pdo
composer install --no-interaction --prefer-dist
npm ci
```

### 2) Configure .env
Create or edit `.env` and set these values (adjust to your environment):
```
APP_NAME=Procurement
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:3000

DB_CONNECTION=sqlsrv
DB_HOST=YOUR_HOSTNAME\\SQLEXPRESS
DB_PORT=1433
DB_DATABASE=Procurement_Database
DB_USERNAME=YourUser
DB_PASSWORD=YourPass

SESSION_DRIVER=file
SESSION_DOMAIN=127.0.0.1
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

VITE_URL=http://127.0.0.1:5173
VITE_DEV_SERVER_URL=http://127.0.0.1:5173
```

Generate the app key:
```
php artisan key:generate
```

### 3) Enable SQL Server driver in PHP (Windows)
In your `php.ini`:
```
extension=pdo_sqlsrv
extension=sqlsrv
```
Restart your web server or PHP process after enabling.

### 4) Database schema and seed

This project works with the provided SQL Server schema. If you already created the tables in SSMS using the supplied SQL, Laravel migrations are idempotent and will not recreate existing tables.

Run migrations (safe for existing tables) and seed:
```
php artisan migrate --ansi
php artisan db:seed --ansi
```

Seeders populate:
- Role types: requestor, superadmin
- Statuses: Draft, Verified, Approved, Received, Rejected
- Users, login, and roles (test accounts below)
- Suppliers (sample), purchase orders, items, and approvals (sample history)

Test accounts (username / password):
- superadmin / superadmin123  (superadmin)
- requestor / requestor123  (requestor)

### 5) Frontend assets
Bootstrap 5.3.7 is wired via Vite.

Dev mode (recommended during development):
```
npm run dev
php artisan serve --host=127.0.0.1 --port=3000
```

Build once (no Vite dev server):
```
npm run build
php artisan serve --host=127.0.0.1 --port=3000
```

Open: `http://127.0.0.1:3000/login`

### Features quick tour
- Auth via `login` table; session holds minimal user info and role.
- Role dashboards with live metrics:
  - Requestor: create PO, view drafts and recent POs
  - Finance: verify POs
  - Department head: approve POs
  - Authorized personnel: receive POs, manage users and suppliers
- Purchase Order create:
  - Select supplier; description dropdown prefilled from previous items
  - “Add new item manually” lets you type a new description and price
  - Unit price auto-fills from previous orders for the same supplier/item
  - Real-time totals panel (shipping, discount, ex‑VAT, VAT, total)
  - PO number auto-generated as `YYYYMMDD-XXX`

### Common setup issues
- 419 Page Expired (CSRF/session): ensure `APP_URL` and `SESSION_DOMAIN` match your browser host, clear caches
```
php artisan optimize:clear
```
- SQL Server driver missing: install ODBC + enable `pdo_sqlsrv` and `sqlsrv` in `php.ini`.
- Foreign key errors when creating PO: make sure you logged in with a seeded account and selected a seeded supplier.

### Useful artisan commands
```
php artisan migrate:fresh --seed --ansi
php artisan tinker
php artisan route:list | cat
```

### Directory overview
```
app/Http/Controllers    # Auth, Dashboard, PO, Suppliers, Approvals
database/seeders        # RoleTypes, Statuses, Users, Suppliers, POs, Items, Approvals
resources/views         # Blade views (auth, dashboards, po, suppliers, admin users)
routes/web.php          # Web routes
```

### Support
If you hit an error, copy the exact message from the browser/console and the latest lines from `storage/logs/laravel.log` and share them.

# SQL Server PDO Connection

![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

A robust and well-documented PHP class for connecting to Microsoft SQL Server using PDO. This project provides a comprehensive solution for managing database connections, with features for configuration loading, connection testing, and detailed troubleshooting.

## Table of Contents

-   [Features](#features)
-   [Prerequisites](#prerequisites)
-   [Installation & Setup](#installation--setup)
-   [Usage](#usage)
-   [Project Structure](#project-structure)
-   [Contributing](#contributing)
-   [License](#license)

## Features

-   **Robust Connection Handling:** A dedicated class for managing SQL Server PDO connections.
-   **Configuration Management:** A `ConfigLoader` class to manage settings from `.env` and config files.
-   **Comprehensive Examples:** Includes scripts demonstrating basic connections, CRUD operations, transactions, and more.
-   **Connection Testing:** A script to test the database connection and provide detailed feedback.
-   **Troubleshooting Guide:** A detailed guide to help resolve common connection issues.

## Prerequisites

-   PHP >= 8.2
-   Composer
-   Microsoft SQL Server
-   PHP extensions:
    -   `pdo`
    -   `pdo_sqlsrv`

## Installation & Setup

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/your-username/pdo-sqlsrv-connection.git
    cd pdo-sqlsrv-connection
    ```

2.  **Install dependencies:**

    ```bash
    composer install
    ```

3.  **Set up the environment file:**

    Create a `.env` file from the example:

    ```bash
    cp .env.example .env
    ```

    Update the `.env` file with your SQL Server database credentials:

    ```ini
    DB_HOST=localhost
    DB_PORT=1433
    DB_DATABASE=Database
    DB_USERNAME=Admin
    DB_PASSWORD=122002
    ```

## Usage

### Testing the Connection

Run the connection test script from the command line:

```bash
php test_connection.php
```

### Simple Connection Example

The `simple_connection_example.php` script provides a minimal example of how to connect to the database:

```bash
php simple_connection_example.php
```

### Comprehensive Usage Examples

The `example_usage.php` script demonstrates various ways to use the `SqlServerPDOConnection` class, including:

-   Creating a table
-   Inserting data with prepared statements
-   Selecting data with different fetch modes
-   Updating data
-   Using transactions
-   Executing aggregate functions

```bash
php example_usage.php
```

## Project Structure

```
.
├── app/                  # Core application files (if this were a full Laravel project)
├── bootstrap/            # Application bootstrap scripts
├── config/               # Configuration files
├── database/             # Database-related files
├── logs/                 # Log files
├── public/               # Publicly accessible files
├── resources/            # Application resources
├── routes/               # Route definitions
├── storage/              # Storage directories
├── tests/                # Test files
├── vendor/               # Composer dependencies
├── .env                  # Environment variables
├── .env.example          # Example environment file
├── CHANGELOG.md          # Project changelog
├── composer.json         # Composer dependencies
├── config.php            # Main configuration file
├── ConfigLoader.php      # Configuration loader class
├── example_usage.php     # Comprehensive usage examples
├── INSTALLATION_GUIDE.md # Installation guide
├── README.md             # This file
├── simple_connection_example.php # Simple connection example
├── SqlServerPDOConnection.php # Main connection class
├── test_connection.php   # Connection test script
└── TROUBLESHOOTING_GUIDE.md # Troubleshooting guide
```

## Contributing

Contributions are welcome! Please feel free to submit a pull request.

## License

This project is licensed under the MIT License.

## Appendix: Optional UI change — supplier dropdown and VAT checkboxes (documentation only)

The following shows how to implement your requested UI/behavior without altering this repository. You can copy these snippets into your project to:
- Remove the `@foreach` that populates the supplier dropdown
- Replace the two text inputs for “Vatable Sales (Ex Vat)” and “12% Vat” with non-required checkboxes that do not block saving when unchecked

### Blade: `resources/views/po/create.blade.php` (replace relevant fragments)

1) Supplier dropdown without `@foreach` (leave an empty select to be filled by JS later, or keep a single placeholder option):

```blade
<label for="supplier_id" class="form-label">Supplier</label>
<select name="supplier_id" id="supplier_id" class="form-select" required>
    <option value="" selected disabled>Select supplier…</option>
    {{-- Options can be injected dynamically via JS or left empty intentionally --}}
</select>
```

2) Replace read-only text inputs for totals with non-required checkboxes. These are purely user preferences and do not affect backend validation; saving works even if they are unchecked:

```blade
<div class="form-check mt-3">
    <input class="form-check-input" type="checkbox" value="1" id="cb-vatable-sales" name="vatable_sales">
    <label class="form-check-label" for="cb-vatable-sales">
        Vatable Sales (Ex Vat)
    </label>
    {{-- Optional helper text can go here; no validation required --}}
  </div>

  <div class="form-check">
    <input class="form-check-input" type="checkbox" value="1" id="cb-apply-vat12" name="apply_vat_12">
    <label class="form-check-label" for="cb-apply-vat12">
        12% Vat
    </label>
  </div>
```

Note: Remove or comment out the previous read-only inputs like `#calc-subtotal` and `#calc-vat` in the form section you are replacing.

### Controller: `app/Http/Controllers/PurchaseOrderController.php` (accept new optional checkboxes)

In your `store` method, keep existing validation for supplier, purpose, dates, and items. Add optional handling for the new checkboxes so form submissions succeed regardless of their presence:

```php
// Inside store(Request $request)
$userSelectedVatableSales = $request->boolean('vatable_sales');
$userSelectedApplyVat12 = $request->boolean('apply_vat_12');

// The backend continues to compute monetary totals server-side. The two flags are optional
// and do not participate in validation. You may persist them as metadata if desired:
// DB::table('purchase_orders')->insert([
//   ... existing fields ...
//   'meta_vatable_sales' => $userSelectedVatableSales ? 1 : 0,
//   'meta_apply_vat_12' => $userSelectedApplyVat12 ? 1 : 0,
// ]);

// If you do not want to store them, simply read them and ignore.
```

No changes are required to validation rules because the checkboxes are optional by default. If the inputs are absent or unchecked, `$request->boolean()` returns `false`, and saving proceeds as normal.

