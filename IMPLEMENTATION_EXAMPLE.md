# Configuration Implementation Examples

## Quick Start: How to Use New Config Files

### Example 1: Updating DashboardController

**Before:**
```php
// File: app/Http/Controllers/DashboardController.php
->limit(5)
->limit(10)
private const CACHE_DURATION = 300;
```

**After:**
```php
// File: app/Http/Controllers/DashboardController.php
->limit(config('ui.pagination.small'))
->limit(config('ui.pagination.default'))
private const CACHE_DURATION = config('ui.cache.dashboard');
```

### Example 2: Updating SuperAdminController

**Before:**
```php
// File: app/Http/Controllers/SuperAdminController.php
set_time_limit(10);
set_time_limit(30);
->limit(20)
->limit(5)
->limit(10)
```

**After:**
```php
// File: app/Http/Controllers/SuperAdminController.php
set_time_limit(config('ui.timeouts.api_seconds'));
set_time_limit(config('ui.timeouts.database_seconds'));
->limit(config('ui.pagination.large'))
->limit(config('ui.pagination.small'))
->limit(config('ui.pagination.recent'))
```

### Example 3: Passing Config to JavaScript

**Before:**
```javascript
// resources/js/dashboards/superadmin-dashboard-enhanced.js
setInterval(() => {
    this.refreshSystemMetrics(true);
}, 30000); // Hardcoded 30 seconds
```

**After:**
```blade
{{-- resources/views/dashboards/superadmin.blade.php --}}
@push('scripts')
    <script>
        window.APP_CONFIG = {
            autoRefreshEnabled: {{ config('ui.auto_refresh.enabled') ? 'true' : 'false' }},
            autoRefreshIntervalMs: {{ config('ui.auto_refresh.interval_ms') }},
            reloadDelayMs: {{ config('ui.delays.reload_ms') }},
            notificationDurationMs: {{ config('ui.notifications.success_duration_ms') }}
        };
    </script>
    @vite(['resources/js/dashboards/superadmin-dashboard-enhanced.js'])
@endpush
```

```javascript
// resources/js/dashboards/superadmin-dashboard-enhanced.js
startAutoRefresh() {
    if (!window.APP_CONFIG.autoRefreshEnabled) return;
    
    setInterval(() => {
        this.refreshSystemMetrics(true);
    }, window.APP_CONFIG.autoRefreshIntervalMs);
}

async someAction() {
    // ...
    setTimeout(() => location.reload(), window.APP_CONFIG.reloadDelayMs);
}
```

### Example 4: Using Role Constants

**Before:**
```php
// Multiple files
if ($auth['role'] === 'superadmin') { }
if ($auth['role'] === 'requestor') { }
DB::table('role_types')->where('user_role_type', 'superadmin')
```

**After:**
```php
// Multiple files
if ($auth['role'] === config('roles.types.SUPERADMIN')) { }
if ($auth['role'] === config('roles.types.REQUESTOR')) { }
DB::table('role_types')->where('user_role_type', config('roles.types.SUPERADMIN'))
```

Or better yet, create a helper class:

```php
// app/Helpers/Role.php
<?php

namespace App\Helpers;

class Role
{
    public static function isSuperAdmin(?string $role): bool
    {
        return $role === config('roles.types.SUPERADMIN');
    }
    
    public static function isRequestor(?string $role): bool
    {
        return $role === config('roles.types.REQUESTOR');
    }
    
    public static function superadmin(): string
    {
        return config('roles.types.SUPERADMIN');
    }
    
    public static function requestor(): string
    {
        return config('roles.types.REQUESTOR');
    }
}
```

Then use it:
```php
use App\Helpers\Role;

if (Role::isSuperAdmin($auth['role'])) { }
DB::table('role_types')->where('user_role_type', Role::superadmin())
```

### Example 5: Notification Timeouts

**Before:**
```javascript
// resources/js/dashboards/superadmin-dashboard-enhanced.js
setTimeout(() => {
    if (notification.parentNode) {
        notification.remove();
    }
}, 5000); // Hardcoded 5 seconds
```

**After:**
```javascript
// Use config passed from Blade
setTimeout(() => {
    if (notification.parentNode) {
        notification.remove();
    }
}, window.APP_CONFIG.notificationDurationMs);
```

## Full File Update Example

### DashboardController.php - Complete Refactoring

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\SystemMonitoringService;

class DashboardController extends Controller
{
    /**
     * Cache duration for dashboard metrics (in seconds)
     * Loaded from config instead of hardcoded
     */
    private const CACHE_DURATION = null; // Will use config value
    
    private function getCacheDuration(): int
    {
        return config('ui.cache.dashboard');
    }

    public function index(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth) {
            return redirect()->route('login');
        }

        $data = ['auth' => $auth];

        if ($auth['role'] === config('roles.types.REQUESTOR')) {
            // Requestor dashboard - use small pagination limit
            $data['recentPOs'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', function($join) {
                    $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                         ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                })
                ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->where('po.requestor_id', $auth['user_id'])
                ->whereNotNull('st.status_name')
                ->select('po.purchase_order_no', 'po.purpose', 'st.status_name', 'po.total', 's.name as supplier_name')
                ->orderByDesc('po.created_at')
                ->limit(config('ui.pagination.small')) // ← Now configurable
                ->get();

            $data['metrics'] = $this->getCachedMetrics($auth['user_id'], 'requestor', function() use ($auth) {
                return [
                    'total_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('po.requestor_id', $auth['user_id'])
                        ->whereNotNull('st.status_name')->count(),
                    'pending_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('po.requestor_id', $auth['user_id'])
                        ->where('st.status_name', 'Pending')
                        ->whereNotNull('st.status_name')->count(),
                ];
            });
        }
        elseif ($auth['role'] === config('roles.types.SUPERADMIN')) { // ← Now using config
            // Superadmin dashboard - use small pagination limit for recent items
            $data['recentPOs'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', function($join) {
                    $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                         ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                })
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->whereNotNull('st.status_name')
                ->select('po.*', 'st.status_name', 'st.status_id')
                ->orderByDesc('po.created_at')
                ->limit(config('ui.pagination.small')) // ← Now configurable
                ->get();
                
            $data['suppliers'] = DB::table('suppliers')
                ->orderBy('name')
                ->limit(config('ui.pagination.small')) // ← Now configurable
                ->get();
                
            $data['users'] = DB::table('users')
                ->leftJoin('login', 'users.user_id', '=', 'login.user_id')
                ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
                ->leftJoin('role_types', 'role_types.role_type_id', '=', 'roles.role_type_id')
                ->select('users.name', 'users.position', 'users.email', 'users.created_at', 'login.username', 'role_types.user_role_type as role')
                ->groupBy('users.user_id', 'users.name', 'users.position', 'users.email', 'users.created_at', 'login.username', 'role_types.user_role_type')
                ->orderBy('users.created_at', 'desc')
                ->limit(config('ui.pagination.small')) // ← Now configurable
                ->get();

            $data['metrics'] = $this->getCachedMetrics($auth['user_id'], 'superadmin', function() {
                return [
                    'total_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->whereNotNull('st.status_name')->count(),
                    'pending_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('st.status_name', 'Pending')
                        ->whereNotNull('st.status_name')->count(),
                    'suppliers' => DB::table('suppliers')->count(),
                    'users' => DB::table('users')->count(),
                ];
            });

            // System monitoring metrics
            try {
                $monitor = new SystemMonitoringService();
                $data['systemMetrics'] = $monitor->getSystemMetrics();
            } catch (\Throwable $e) {
                $data['systemMetrics'] = [];
            }
        }

        return match ($auth['role']) {
            config('roles.types.REQUESTOR') => view('dashboards.requestor', $data), // ← Using config
            config('roles.types.SUPERADMIN') => view('dashboards.superadmin', $data), // ← Using config
            default => view('dashboard', ['auth' => $auth]),
        };
    }

    private function getCachedMetrics(string $userId, string $role, callable $callback)
    {
        $cacheKey = "dashboard_metrics_{$role}_{$userId}";
        return Cache::remember($cacheKey, $this->getCacheDuration(), $callback); // ← Using config
    }

    public function getMetrics(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($auth['role'] === config('roles.types.REQUESTOR')) { // ← Using config
            $metrics = $this->getCachedMetrics($auth['user_id'], 'requestor', function() use ($auth) {
                // ... metrics calculation
            });
        } elseif ($auth['role'] === config('roles.types.SUPERADMIN')) { // ← Using config
            $metrics = $this->getCachedMetrics($auth['user_id'], 'superadmin', function() {
                // ... metrics calculation
            });
        }

        return response()->json(['data' => $metrics]);
    }
}
```

## Testing Your Changes

### 1. Test Config Loading

```bash
php artisan tinker
```

```php
>>> config('ui.pagination.default')
=> 10

>>> config('ui.auto_refresh.interval_ms')
=> 30000

>>> config('roles.types.SUPERADMIN')
=> "superadmin"
```

### 2. Test Environment Variable Override

Add to `.env`:
```env
UI_PAGINATION_DEFAULT=15
```

```bash
php artisan config:clear
php artisan tinker
```

```php
>>> config('ui.pagination.default')
=> 15  // Now reads from .env!
```

### 3. Test in Browser

1. Access superadmin dashboard
2. Open browser console
3. Check `window.APP_CONFIG` is defined
4. Verify auto-refresh uses configured interval

## Gradual Migration Strategy

You don't need to update everything at once. Migrate gradually:

### Week 1: Core Controllers
- DashboardController.php
- SuperAdminController.php (timeouts and limits)

### Week 2: JavaScript Files
- superadmin-dashboard-enhanced.js
- status-color-sync.js

### Week 3: Other Controllers
- PurchaseOrderController.php
- SecurityController.php
- ItemsController.php

### Week 4: Blade Templates
- Replace hardcoded labels
- Pass config to JavaScript

## Summary

**Created Files:**
- `config/ui.php` - UI behavior configuration
- `config/roles.php` - Role definitions
- `HARDCODED_VALUES_AUDIT.md` - Complete audit report
- `CONFIGURATION_GUIDE.md` - How to use configs
- `IMPLEMENTATION_EXAMPLE.md` - This file

**Next Steps:**
1. Review the audit report
2. Start with DashboardController example above
3. Gradually migrate other files
4. Test thoroughly after each change
5. Update .env.example with new options

**Benefits:**
✅ Centralized configuration
✅ Easy customization via .env
✅ Better maintainability
✅ Production-ready caching
✅ Follows Laravel best practices
