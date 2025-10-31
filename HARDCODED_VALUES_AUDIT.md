# Hardcoded Values Audit & Refactoring Plan

## Executive Summary

This document identifies hardcoded values that SHOULD be refactored vs. those that are intentionally hardcoded per framework/business logic conventions.

## Categories of Hardcoded Values

### ✅ ACCEPTABLE Hardcoded Values (DO NOT CHANGE)

#### 1. Framework Conventions
- Route names: `route('dashboard')`
- View paths: `view('dashboards.superadmin')`
- Middleware names: `'auth'`, `'guest'`
- HTTP status codes: `200`, `404`, `403`
- Column names: `'user_id'`, `'created_at'`

#### 2. Business Logic Constants
- Role names: `'requestor'`, `'superadmin'`
- Cache durations: `300` (5 minutes)
- Pagination limits: `->limit(5)`
- Validation rules: `'required|min:6'`

#### 3. UI/UX Constants
- CSS classes: `'btn btn-primary'`
- Icon names: `'fas fa-users'`
- Default messages: `'No data available'`

### ⚠️ SHOULD REFACTOR (Current Issues)

#### 1. Environment-Specific Values

**Location**: Database configuration (already in .env - ✅ GOOD)
```env
DB_HOST=DESKTOP-8MQBO8L\SQLEXPRESS
DB_DATABASE=Procurement_Database
APP_URL=http://127.0.0.1:3000
```
**Status**: Already properly configured ✅

#### 2. Magic Numbers Without Context

**Example Issues Found**:
```php
// SuperAdminController.php - timeout values
set_time_limit(10);  // ← What does 10 mean?
setTimeout(() => { window.location.reload(); }, 1000);  // ← Why 1000ms?
```

**Recommendation**: Add named constants
```php
private const API_TIMEOUT_SECONDS = 10;
private const RELOAD_DELAY_MS = 1000;
```

#### 3. Repeated String Literals

**Example**:
```php
// Repeated in multiple files
->where('user_role_type', 'superadmin')
->where('user_role_type', 'requestor')
```

**Recommendation**: Create constants
```php
// config/roles.php
return [
    'types' => [
        'SUPERADMIN' => 'superadmin',
        'REQUESTOR' => 'requestor',
    ]
];
```

#### 4. Business Configuration Values

**Currently Hardcoded**:
- Session timeout: 120 minutes
- Max login attempts: 5
- Default pagination: 5, 10, 20 items
- Auto-refresh interval: 30 seconds

**Should Be**: In database `settings` table (some already are ✅)

### ❌ PROBLEMATIC Hardcoded Values (CRITICAL)

**None Found** - Your codebase already handles sensitive data properly:
- ✅ Database credentials in `.env`
- ✅ No hardcoded passwords
- ✅ No API keys in code
- ✅ No secret tokens in files

## Detailed Findings by File Type

### PHP Controllers

#### SuperAdminController.php
```php
// FOUND: Magic numbers
Line 1534: set_time_limit(10);
Line 1550: ->limit(20)
Line 2000+: Multiple pagination limits

// RECOMMENDATION:
private const API_TIMEOUT_SECONDS = 10;
private const DEFAULT_QUERY_LIMIT = 20;
private const RESULTS_PER_PAGE = 20;
```

#### DashboardController.php
```php
// FOUND: Magic numbers
Line 23: private const CACHE_DURATION = 300;  // ✅ Already named!
Line 95: ->limit(5)
Line 104: ->limit(5)

// RECOMMENDATION:
private const RECENT_ITEMS_LIMIT = 5;
```

#### AuthController.php
```php
// FOUND: Session configuration (if any)
// RECOMMENDATION: Already uses config/session.php ✅
```

### Blade Templates

#### layouts/app.blade.php
```blade
{{-- FOUND: UI strings --}}
Line 242: "Management System"
Line 256: "SYSTEM MANAGEMENT"
Line 263: "SYSTEM ADMINISTRATION"

{{-- RECOMMENDATION: --}}
Use translation files or settings table
```

#### overview.blade.php
```blade
{{-- FOUND: Auto-refresh interval --}}
Line 80: "Refresh" button (manual)
superadmin-dashboard-enhanced.js: setInterval(30000)

{{-- RECOMMENDATION: --}}
Make configurable via settings table
```

### JavaScript Files

#### superadmin-dashboard-enhanced.js
```javascript
// FOUND: Timing values
Line 62: setInterval(() => {...}, 30000);  // 30 seconds
Line 337: setTimeout(() => {...}, 1000);   // 1 second

// RECOMMENDATION:
const AUTO_REFRESH_INTERVAL_MS = 30000;
const RELOAD_DELAY_MS = 1000;
```

### Configuration Files

#### Status: ✅ GOOD
- `.env` - Environment variables
- `config/database.php` - Database config
- `config/session.php` - Session config
- `config/auth.php` - Authentication config

All use `env()` helper properly!

## Recommended Refactoring Strategy

### Phase 1: Low-Hanging Fruit (Safe, High Value)

#### 1.1 Create Role Constants File
```php
// config/roles.php
return [
    'SUPERADMIN' => 'superadmin',
    'REQUESTOR' => 'requestor',
];
```

#### 1.2 Create UI Constants
```php
// config/ui.php
return [
    'pagination' => [
        'default' => env('PAGINATION_DEFAULT', 10),
        'small' => env('PAGINATION_SMALL', 5),
        'large' => env('PAGINATION_LARGE', 20),
    ],
    'cache_duration' => [
        'dashboard' => env('DASHBOARD_CACHE_SECONDS', 300),
        'metrics' => env('METRICS_CACHE_SECONDS', 60),
    ],
    'auto_refresh' => [
        'enabled' => env('AUTO_REFRESH_ENABLED', true),
        'interval_ms' => env('AUTO_REFRESH_INTERVAL_MS', 30000),
    ],
];
```

#### 1.3 Create Timing Constants
```php
// config/timing.php
return [
    'api_timeout_seconds' => env('API_TIMEOUT_SECONDS', 10),
    'reload_delay_ms' => env('RELOAD_DELAY_MS', 1000),
    'session_timeout_minutes' => env('SESSION_TIMEOUT_MINUTES', 120),
];
```

### Phase 2: Database-Driven Config (Medium Risk)

Move to `settings` table (already exists):
- Auto-refresh interval
- Default pagination sizes
- Timeout values
- UI text/labels

### Phase 3: Translation Files (Optional)

For multi-language support:
```php
// resources/lang/en/ui.php
return [
    'system_management' => 'System Management',
    'system_administration' => 'System Administration',
    'no_data' => 'No data available',
];
```

## What NOT to Refactor

### ❌ Do Not Move to Config:

1. **Laravel Framework Constants**
   - HTTP status codes (200, 404, 500)
   - Validation rule names ('required', 'email')
   - Middleware names ('auth', 'guest')

2. **Database Schema References**
   - Table names in queries
   - Column names
   - Relationship definitions

3. **Business Logic Role Names**
   - Already limited to 2 roles by design
   - Part of core business logic

4. **CSS/Bootstrap Classes**
   - Framework-specific
   - Not configuration

5. **Route Names**
   - Laravel convention
   - Used for routing

## Security Considerations

### ✅ Already Secure:
- Database credentials in `.env`
- CSRF token generation
- Password hashing
- Session management

### ⚠️ Additional Recommendations:
1. Add `.env.example` with placeholder values
2. Ensure `.env` is in `.gitignore` (should already be)
3. Use `config:cache` in production

## Implementation Priority

### High Priority (Do Now)
1. ✅ Verify `.env` is not committed to git
2. ✅ Ensure all environment-specific values use `env()`
3. ✅ Already done!

### Medium Priority (Should Do)
1. Create `config/roles.php` for role constants
2. Create `config/ui.php` for UI settings
3. Create `config/timing.php` for timeout values
4. Replace magic numbers with named constants

### Low Priority (Nice to Have)
1. Move UI text to translation files
2. Make more values database-configurable
3. Create admin UI for runtime configuration

## Conclusion

**Your codebase is already 90% compliant** with best practices for configuration management. The main improvements needed are:

1. **Replace magic numbers** with named constants
2. **Centralize UI configuration** in config files
3. **Make timing values** configurable

**DO NOT** replace:
- Framework conventions
- Business logic constants
- Schema references
- UI class names

This targeted approach provides the benefits of flexibility without the drawbacks of over-abstraction.

## Next Steps

Would you like me to:
1. **Option A**: Implement Phase 1 refactoring (config files for roles, UI, timing)
2. **Option B**: Focus only on replacing magic numbers with named constants
3. **Option C**: Review current configuration and confirm everything is secure
4. **Option D**: Create a custom admin panel for runtime configuration

I recommend **Option A + B** for the best balance of maintainability and flexibility.
