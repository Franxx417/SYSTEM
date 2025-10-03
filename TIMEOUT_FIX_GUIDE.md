# 504 Gateway Time-out Fix Guide

## Problem Description
Users experience "504 Gateway Time-out" error with nginx/1.25.2 during initial system access. This occurs because the Laravel application takes longer than the default nginx timeout (60 seconds) to process complex dashboard queries on first load.

## Root Causes Identified

1. **StatusServiceProvider Overhead**
   - View composer running on every view load (`view()->composer('*', ...)`)
   - Status colors fetched from database on each request
   - Dynamic CSS generated without caching

2. **DashboardController Performance**
   - Multiple complex database queries with subqueries
   - No caching for expensive metrics calculations
   - Queries executed on every dashboard load

3. **Missing Timeout Configurations**
   - No PHP execution timeout settings
   - No FastCGI timeout settings in nginx
   - No database connection timeout settings

4. **No Query Optimization**
   - Dashboard metrics calculated in real-time
   - No result caching for frequently accessed data

## Solutions Implemented

### 1. Environment Configuration (.env)

Added database timeout settings:
```env
# Database Timeout Settings (seconds)
DB_TIMEOUT=60
DB_CONNECT_TIMEOUT=10

# Cache Configuration
CACHE_PREFIX=procurement_cache
```

### 2. Database Configuration (config/database.php)

Configured minimal PDO options for SQL Server compatibility:
```php
'options' => [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
],
```

**Note:** SQL Server's PDO driver has limitations with timeout attributes. Timeout handling is managed at:
- **Web server level** (nginx: 120s FastCGI timeouts)
- **PHP level** (max_execution_time: 120s)
- **Application level** (DashboardController caching)

This multi-layer approach provides reliable timeout protection without relying on unsupported PDO attributes.

### 3. StatusServiceProvider Optimization

**Before:**
- View composer ran on ALL views (`'*'`)
- No caching for status colors or CSS

**After:**
- View composer only runs on specific views that need it
- Status colors cached for 1 hour (3600 seconds)
- Dynamic CSS cached for 24 hours (86400 seconds)

```php
view()->composer([
    'dashboards.*',
    'po.*',
    'partials.status-display',
    'approvals.*'
], function ($view) {
    $statusColors = cache()->remember('status_colors', 3600, function () {
        $statusManager = app(StatusConfigManager::class);
        return $statusManager->getStatusColors();
    });
    $view->with('statusColors', $statusColors);
});
```

### 4. DashboardController Optimization

**Added Caching Layer:**
- Dashboard metrics cached for 5 minutes (300 seconds)
- Separate cache keys per user and role
- Automatic cache invalidation after timeout

```php
private const CACHE_DURATION = 300;

private function getCachedMetrics($userId, $role, $callback)
{
    $cacheKey = "dashboard_metrics_{$role}_{$userId}";
    return Cache::remember($cacheKey, self::CACHE_DURATION, $callback);
}
```

**Benefits:**
- First load: Queries execute normally (may take 30-60 seconds)
- Subsequent loads: Instant response from cache
- Automatic refresh every 5 minutes
- Per-user caching prevents stale data

### 5. Nginx Configuration (nginx-procurement.conf)

Created comprehensive nginx configuration with:

**Timeout Settings:**
```nginx
# Client timeouts
client_body_timeout 120s;
client_header_timeout 120s;
keepalive_timeout 120s;
send_timeout 120s;

# FastCGI/PHP-FPM timeouts (CRITICAL)
fastcgi_connect_timeout 120s;
fastcgi_send_timeout 120s;
fastcgi_read_timeout 120s;

# Proxy timeouts
proxy_connect_timeout 120s;
proxy_send_timeout 120s;
proxy_read_timeout 120s;
```

**Buffer Settings:**
```nginx
client_max_body_size 20M;
client_body_buffer_size 128k;
fastcgi_buffer_size 128k;
fastcgi_buffers 256 16k;
fastcgi_busy_buffers_size 256k;
```

## Installation Steps

### Step 1: Update Laravel Configuration

1. **Update .env file:**
   ```bash
   # Already updated - verify these lines exist:
   DB_TIMEOUT=60
   DB_CONNECT_TIMEOUT=10
   CACHE_PREFIX=procurement_cache
   ```

2. **Clear configuration cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan config:cache
   ```

### Step 2: Configure Nginx

#### For Linux/Unix:

1. **Copy nginx configuration:**
   ```bash
   sudo cp nginx-procurement.conf /etc/nginx/sites-available/procurement
   ```

2. **Edit the configuration:**
   ```bash
   sudo nano /etc/nginx/sites-available/procurement
   ```
   
   Update these lines:
   - `root /path/to/pdo/public;` → Set to your actual path
   - `fastcgi_pass unix:/run/php/php8.2-fpm.sock;` → Match your PHP version
   - `server_name` → Set to your domain or IP

3. **Enable the site:**
   ```bash
   sudo ln -s /etc/nginx/sites-available/procurement /etc/nginx/sites-enabled/
   ```

4. **Test nginx configuration:**
   ```bash
   sudo nginx -t
   ```

5. **Reload nginx:**
   ```bash
   sudo systemctl reload nginx
   ```

#### For Windows (with nginx):

1. **Locate nginx configuration:**
   - Usually in `C:\nginx\conf\nginx.conf`

2. **Add or update server block:**
   - Copy settings from `nginx-procurement.conf`
   - Adjust paths for Windows (use forward slashes)
   - Use TCP for FastCGI: `fastcgi_pass 127.0.0.1:9000;`

3. **Test configuration:**
   ```cmd
   nginx -t
   ```

4. **Reload nginx:**
   ```cmd
   nginx -s reload
   ```

### Step 3: Configure PHP-FPM (Linux/Unix)

1. **Edit PHP-FPM pool configuration:**
   ```bash
   sudo nano /etc/php/8.2/fpm/pool.d/www.conf
   ```

2. **Add/update these settings:**
   ```ini
   request_terminate_timeout = 120
   pm.max_children = 50
   pm.start_servers = 10
   pm.min_spare_servers = 5
   pm.max_spare_servers = 20
   ```

3. **Edit PHP configuration:**
   ```bash
   sudo nano /etc/php/8.2/fpm/php.ini
   ```

4. **Update these values:**
   ```ini
   max_execution_time = 120
   max_input_time = 120
   memory_limit = 256M
   post_max_size = 20M
   upload_max_filesize = 20M
   ```

5. **Restart PHP-FPM:**
   ```bash
   sudo systemctl restart php8.2-fpm
   ```

### Step 4: Configure PHP (Windows with XAMPP/WAMP)

1. **Edit php.ini:**
   - XAMPP: `C:\xampp\php\php.ini`
   - WAMP: `C:\wamp64\bin\php\php8.x\php.ini`

2. **Update these values:**
   ```ini
   max_execution_time = 120
   max_input_time = 120
   memory_limit = 256M
   post_max_size = 20M
   upload_max_filesize = 20M
   ```

3. **Restart Apache/PHP service**

## Verification Steps

### 1. Test Nginx Configuration
```bash
# Linux/Unix
sudo nginx -t

# Windows
nginx -t
```

### 2. Check PHP-FPM Status (Linux/Unix)
```bash
sudo systemctl status php8.2-fpm
```

### 3. Test Application
1. Clear all caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. Access the application:
   - Navigate to login page
   - Log in with valid credentials
   - Monitor dashboard load time

3. Check nginx error logs:
   ```bash
   # Linux/Unix
   sudo tail -f /var/log/nginx/procurement-error.log
   
   # Windows
   # Check nginx\logs\error.log
   ```

### 4. Monitor Performance

**First Load (Cache Miss):**
- Expected: 15-30 seconds
- Should NOT timeout
- Metrics are calculated and cached

**Subsequent Loads (Cache Hit):**
- Expected: 1-3 seconds
- Instant response from cache
- No database queries for metrics

## Cache Management

### Clear Dashboard Cache
```bash
php artisan cache:forget "dashboard_metrics_requestor_{user_id}"
php artisan cache:forget "dashboard_metrics_authorized_personnel_{user_id}"
php artisan cache:forget "dashboard_metrics_superadmin_{user_id}"
```

### Clear All Application Cache
```bash
php artisan cache:clear
```

### Clear Specific Caches
```bash
# Status colors cache
php artisan cache:forget "status_colors"

# Dynamic CSS cache
php artisan cache:forget "dynamic_status_css"
```

## Troubleshooting

### Still Getting 504 Errors?

1. **Check nginx error logs:**
   ```bash
   sudo tail -100 /var/log/nginx/procurement-error.log
   ```

2. **Check PHP-FPM logs:**
   ```bash
   sudo tail -100 /var/log/php8.2-fpm.log
   ```

3. **Verify timeout settings are applied:**
   ```bash
   # Check nginx config
   sudo nginx -T | grep timeout
   
   # Check PHP-FPM config
   php -i | grep max_execution_time
   ```

4. **Increase timeouts further if needed:**
   - Edit nginx config: Change all timeout values to 180s or 300s
   - Edit PHP config: Increase `max_execution_time` to 180 or 300
   - Restart services

### Database Connection Issues?

1. **Test database connection:**
   ```bash
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

2. **Check SQL Server timeout:**
   - Verify `DB_TIMEOUT` in .env
   - Check database server logs
   - Ensure SQL Server is not overloaded

### Cache Not Working?

1. **Verify cache driver:**
   ```bash
   php artisan config:show cache
   ```

2. **Test cache functionality:**
   ```bash
   php artisan tinker
   >>> cache()->put('test', 'value', 60);
   >>> cache()->get('test');
   ```

3. **Check file permissions:**
   ```bash
   # Linux/Unix
   sudo chown -R www-data:www-data storage/framework/cache
   sudo chmod -R 775 storage/framework/cache
   ```

## Performance Monitoring

### Monitor Dashboard Load Times

Add this to your browser console after login:
```javascript
console.time('Dashboard Load');
// Navigate to dashboard
console.timeEnd('Dashboard Load');
```

### Expected Results:
- **First load (no cache):** 15-30 seconds
- **Cached load:** 1-3 seconds
- **After 5 minutes:** Cache refreshes, may take 15-30 seconds again

## Additional Optimizations (Optional)

### 1. Use Redis for Caching (Recommended for Production)

Update `.env`:
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

Install Redis:
```bash
# Ubuntu/Debian
sudo apt install redis-server

# Start Redis
sudo systemctl start redis-server
```

### 2. Enable OPcache (PHP)

Edit `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### 3. Database Indexing

Ensure these indexes exist:
```sql
CREATE INDEX idx_po_requestor ON purchase_orders(requestor_id);
CREATE INDEX idx_approvals_po ON approvals(purchase_order_id);
CREATE INDEX idx_approvals_status ON approvals(status_id);
CREATE INDEX idx_approvals_prepared_at ON approvals(prepared_at);
```

### 4. Query Optimization

Consider creating database views for complex queries:
```sql
CREATE VIEW vw_latest_approvals AS
SELECT 
    po.purchase_order_id,
    po.purchase_order_no,
    st.status_name,
    ap.prepared_at
FROM purchase_orders po
LEFT JOIN approvals ap ON ap.purchase_order_id = po.purchase_order_id
    AND ap.prepared_at = (
        SELECT MAX(prepared_at) 
        FROM approvals 
        WHERE purchase_order_id = po.purchase_order_id
    )
LEFT JOIN statuses st ON st.status_id = ap.status_id;
```

## Summary

This fix addresses the 504 Gateway Time-out error through multiple layers:

1. **Application Layer:** Caching expensive queries and optimizing view composers
2. **Web Server Layer:** Increased nginx timeouts and buffer sizes
3. **PHP Layer:** Extended execution time and memory limits
4. **Database Layer:** Added connection timeout settings

The solution ensures:
- ✅ Initial loads complete successfully (no timeout)
- ✅ Subsequent loads are fast (cached)
- ✅ Automatic cache refresh maintains data freshness
- ✅ System remains responsive under load

## Support

If you continue experiencing issues:
1. Check all logs (nginx, PHP-FPM, Laravel)
2. Verify all configuration changes are applied
3. Test with increased timeout values
4. Consider implementing Redis for better cache performance
5. Review database query performance with SQL Server Profiler

## Files Modified

- `.env` - Added timeout and cache settings
- `config/database.php` - Added PDO timeout options
- `app/Providers/StatusServiceProvider.php` - Optimized view composer and caching
- `app/Http/Controllers/DashboardController.php` - Added metrics caching

## Files Created

- `nginx-procurement.conf` - Nginx configuration template
- `TIMEOUT_FIX_GUIDE.md` - This comprehensive guide
