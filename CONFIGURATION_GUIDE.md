# Configuration Management Guide

## Overview

This guide explains the configuration system for the Procurement application and what can/cannot be customized.

## ✅ Created Configuration Files

### 1. `config/ui.php` - UI Behavior Configuration

Centralizes all UI-related settings:

**Pagination**
```php
config('ui.pagination.default')     // 10 items (general listings)
config('ui.pagination.small')       // 5 items (dashboard widgets)
config('ui.pagination.large')       // 20 items (admin tables)
config('ui.pagination.autocomplete') // 200 items (dropdowns)
```

**Cache Durations**
```php
config('ui.cache.dashboard')  // 300 seconds (5 minutes)
config('ui.cache.metrics')    // 60 seconds (1 minute)
```

**Auto-Refresh**
```php
config('ui.auto_refresh.enabled')      // true/false
config('ui.auto_refresh.interval_ms')  // 30000 (30 seconds)
```

**Notifications**
```php
config('ui.notifications.success_duration_ms')  // 5000ms
config('ui.notifications.error_duration_ms')    // 5000ms
config('ui.notifications.alert_duration_ms')    // 5000ms
```

**Delays**
```php
config('ui.delays.reload_ms')            // 1000ms (page reload)
config('ui.delays.modal_init_ms')        // 100ms (modal init)
config('ui.delays.system_restart_ms')    // 3000ms (system restart)
config('ui.delays.session_terminate_ms') // 2000ms (session termination)
```

**Timeouts**
```php
config('ui.timeouts.api_seconds')      // 10 seconds
config('ui.timeouts.database_seconds') // 30 seconds
config('ui.timeouts.upload_seconds')   // 60 seconds
```

### 2. `config/roles.php` - Role Definitions

Defines the two supported roles:

```php
config('roles.types.SUPERADMIN')  // 'superadmin'
config('roles.types.REQUESTOR')   // 'requestor'

config('roles.labels.superadmin') // 'Super Admin'
config('roles.permissions.superadmin') // Array of permissions
```

**⚠️ WARNING**: This application is hardcoded for exactly 2 roles. Adding roles requires extensive code changes.

## 🔧 How to Customize

### Method 1: Environment Variables (Recommended)

Add to `.env` file:

```env
# UI Pagination
UI_PAGINATION_DEFAULT=15
UI_PAGINATION_SMALL=10

# Auto-refresh
UI_AUTO_REFRESH_ENABLED=true
UI_AUTO_REFRESH_INTERVAL_MS=60000

# Timeouts
UI_TIMEOUT_API_SECONDS=20
UI_TIMEOUT_DATABASE_SECONDS=60

# Notifications
UI_NOTIFICATION_SUCCESS_MS=3000
UI_DELAY_RELOAD_MS=1500
```

### Method 2: Direct Config File Edit

Edit `config/ui.php` and change default values:

```php
'pagination' => [
    'default' => 15,  // Changed from 10
    'small' => 10,    // Changed from 5
],
```

### Method 3: Runtime Override (Advanced)

In specific controllers:

```php
$limit = config('ui.pagination.large'); // 20
$timeout = config('ui.timeouts.api_seconds'); // 10
```

## ❌ What Should NOT Be Changed

### 1. Laravel Framework Constants

**DO NOT** move these to config:
```php
// HTTP Status Codes
return response()->json($data, 200);
return abort(404);

// Validation Rules
'required|email|min:6'

// Middleware Names
Route::middleware(['auth']);
```

### 2. Database Schema References

**DO NOT** make these configurable:
```php
DB::table('users')
->select('user_id', 'name', 'email')
->where('is_active', true)
```

**Why?** Schema is defined by migrations. Changing table/column names breaks migrations.

### 3. Route Names

**DO NOT** make these configurable:
```php
route('dashboard')
route('login')
redirect()->route('po.index')
```

**Why?** Laravel routing system requires stable route names.

### 4. View Paths

**DO NOT** make these configurable:
```php
view('dashboards.superadmin')
@extends('layouts.app')
```

**Why?** Blade template resolution depends on file paths.

### 5. CSS Class Names

**DO NOT** make these configurable:
```php
class="btn btn-primary"
class="alert alert-success"
```

**Why?** Bootstrap framework classes must match exactly.

## 📝 Usage Examples

### Before (Hardcoded)

```php
// SuperAdminController.php
set_time_limit(10);
$users = DB::table('users')->limit(20)->get();
setTimeout(() => location.reload(), 1000);
```

### After (Configured)

```php
// SuperAdminController.php
set_time_limit(config('ui.timeouts.api_seconds'));
$users = DB::table('users')->limit(config('ui.pagination.large'))->get();
setTimeout(() => location.reload(), config('ui.delays.reload_ms'));
```

## 🔐 Security Best Practices

### ✅ Already Secure

Your application correctly uses:
- `.env` for database credentials
- `.env` for sensitive API keys
- `.env` for environment-specific URLs
- CSRF tokens for forms
- Password hashing

### ⚠️ Additional Recommendations

1. **Never commit `.env` to git**
   ```bash
   # Verify .env is gitignored
   git check-ignore .env
   ```

2. **Use `.env.example` for documentation**
   ```env
   # .env.example
   DB_HOST=your_server_name
   DB_DATABASE=your_database_name
   
   # UI Configuration
   UI_PAGINATION_DEFAULT=10
   UI_AUTO_REFRESH_INTERVAL_MS=30000
   ```

3. **Cache config in production**
   ```bash
   php artisan config:cache
   ```

4. **Rotate secrets regularly**
   - APP_KEY
   - Database passwords
   - API tokens

## 📊 Configuration Hierarchy

Priority (highest to lowest):

1. **Runtime values** (set in code)
2. **Environment variables** (`.env`)
3. **Config file defaults** (`config/*.php`)
4. **Framework defaults** (Laravel internals)

Example:
```php
// If UI_PAGINATION_DEFAULT=15 in .env
config('ui.pagination.default') // Returns: 15

// If not in .env
config('ui.pagination.default') // Returns: 10 (from config/ui.php)
```

## 🧪 Testing Configuration

### Test Environment Variables

```bash
# Test loading config
php artisan tinker
>>> config('ui.pagination.default')
=> 10
>>> config('roles.types.SUPERADMIN')
=> "superadmin"
```

### Verify Cache

```bash
# Clear config cache
php artisan config:clear

# Rebuild cache
php artisan config:cache

# Verify in production
php artisan config:show
```

## 📖 Next Implementation Steps

### To fully integrate these configs, update:

1. **Controllers** - Replace magic numbers with config calls
2. **JavaScript** - Pass config values from PHP to JS
3. **Blade Templates** - Use config for labels and text
4. **Tests** - Update tests to use config values

### Example Updates Needed:

**SuperAdminController.php**
```php
// Current
->limit(20)

// Updated
->limit(config('ui.pagination.large'))
```

**superadmin-dashboard-enhanced.js**
```javascript
// Current
setInterval(() => {...}, 30000);

// Updated - Pass from Blade
<script>
const AUTO_REFRESH_MS = {{ config('ui.auto_refresh.interval_ms') }};
</script>
setInterval(() => {...}, AUTO_REFRESH_MS);
```

**layouts/app.blade.php**
```blade
{{-- Current --}}
<div>Management System</div>

{{-- Updated --}}
<div>{{ config('ui.labels.app_subtitle') }}</div>
```

## 🎯 Summary

### ✅ Created
- `config/ui.php` - All UI behavior settings
- `config/roles.php` - Role definitions

### ✅ Configurable via .env
- Pagination limits
- Cache durations
- Auto-refresh intervals
- Timeouts
- Delays
- Notification durations

### ❌ Not Configurable (By Design)
- Framework conventions
- Database schema
- Route names
- View paths
- CSS classes

### 🔒 Already Secure
- Database credentials in `.env`
- No hardcoded passwords
- Proper CSRF protection
- Password hashing

Your configuration system now provides flexibility where it matters while maintaining Laravel best practices.
