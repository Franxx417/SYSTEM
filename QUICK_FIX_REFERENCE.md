# 504 Timeout - Quick Fix Reference

## Problem
**504 Gateway Time-out** error when accessing the system initially.

## Quick Solution (5 Minutes)

### Step 1: Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

Or use the utility script:
```bash
php clear_cache.php
```

### Step 2: Verify Configuration
```bash
php test_timeout_fix.php
```

### Step 3: Restart Services

**Linux/Unix:**
```bash
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

**Windows (XAMPP):**
- Restart Apache from XAMPP Control Panel
- Or restart nginx if using nginx

### Step 4: Test Application
1. Open browser
2. Navigate to application URL
3. Log in
4. **First load:** May take 15-30 seconds (normal)
5. **Subsequent loads:** Should be 1-3 seconds (cached)

## What Was Fixed

### ✅ Application Layer
- **StatusServiceProvider**: View composer now only runs on specific views
- **DashboardController**: Added 5-minute caching for metrics
- **Cache Strategy**: Status colors cached for 1 hour, CSS for 24 hours

### ✅ Configuration Layer
- **Database Timeouts**: Added 60-second timeout for SQL Server
- **Cache Settings**: Configured cache prefix and duration
- **Environment**: Updated .env with timeout settings

### ✅ Web Server Layer (nginx)
- **FastCGI Timeouts**: Increased to 120 seconds
- **Client Timeouts**: Increased to 120 seconds
- **Buffer Sizes**: Optimized for large responses

## Expected Performance

| Load Type | Expected Time | Status |
|-----------|---------------|--------|
| First Load (No Cache) | 15-30 seconds | ✅ Normal |
| Cached Load | 1-3 seconds | ✅ Fast |
| After 5 Minutes | 15-30 seconds | ✅ Cache Refresh |
| After Cache Clear | 15-30 seconds | ✅ Normal |

## Troubleshooting

### Still Getting 504?

**Check 1: Verify nginx timeout settings**
```bash
sudo nginx -T | grep timeout
```
Should show: `120s` for all timeout values

**Check 2: Verify PHP timeout**
```bash
php -i | grep max_execution_time
```
Should show: `120` or higher

**Check 3: Check error logs**
```bash
# Nginx
sudo tail -50 /var/log/nginx/error.log

# PHP-FPM
sudo tail -50 /var/log/php8.2-fpm.log

# Laravel
tail -50 storage/logs/laravel.log
```

**Check 4: Database connection**
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

### Cache Not Working?

**Verify cache driver:**
```bash
php artisan config:show cache
```

**Test cache manually:**
```bash
php artisan tinker
>>> cache()->put('test', 'value', 60);
>>> cache()->get('test');
```

**Check file permissions (Linux):**
```bash
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
```

### Performance Still Slow?

**Option 1: Increase cache duration**
Edit `DashboardController.php`:
```php
private const CACHE_DURATION = 600; // 10 minutes instead of 5
```

**Option 2: Use Redis (Recommended)**
```bash
# Install Redis
sudo apt install redis-server

# Update .env
CACHE_DRIVER=redis
```

**Option 3: Add database indexes**
```sql
CREATE INDEX idx_po_requestor ON purchase_orders(requestor_id);
CREATE INDEX idx_approvals_po ON approvals(purchase_order_id);
CREATE INDEX idx_approvals_prepared_at ON approvals(prepared_at);
```

## Files Modified

| File | Change |
|------|--------|
| `.env` | Added DB_TIMEOUT, DB_CONNECT_TIMEOUT |
| `config/database.php` | Added PDO timeout options |
| `app/Providers/StatusServiceProvider.php` | Optimized view composer, added caching |
| `app/Http/Controllers/DashboardController.php` | Added metrics caching |

## Files Created

| File | Purpose |
|------|---------|
| `nginx-procurement.conf` | Nginx configuration template |
| `TIMEOUT_FIX_GUIDE.md` | Comprehensive troubleshooting guide |
| `clear_cache.php` | Cache clearing utility |
| `test_timeout_fix.php` | Verification script |
| `QUICK_FIX_REFERENCE.md` | This quick reference |

## Cache Management Commands

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan cache:clear          # Application cache
php artisan config:clear         # Configuration cache
php artisan view:clear           # View cache
php artisan route:clear          # Route cache

# Rebuild caches
php artisan config:cache         # Cache configuration
php artisan route:cache          # Cache routes
php artisan view:cache           # Cache views

# Clear specific dashboard cache for user
php artisan tinker
>>> cache()->forget('dashboard_metrics_requestor_1');
>>> cache()->forget('status_colors');
>>> cache()->forget('dynamic_status_css');
```

## Nginx Configuration Quick Check

**Location:** `/etc/nginx/sites-available/procurement` (Linux)

**Critical Settings:**
```nginx
fastcgi_connect_timeout 120s;
fastcgi_send_timeout 120s;
fastcgi_read_timeout 120s;
client_body_timeout 120s;
```

**Test config:**
```bash
sudo nginx -t
```

**Reload:**
```bash
sudo systemctl reload nginx
```

## PHP Configuration Quick Check

**Location:** `/etc/php/8.2/fpm/php.ini` (Linux)

**Critical Settings:**
```ini
max_execution_time = 120
max_input_time = 120
memory_limit = 256M
```

**Restart PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
```

## Monitoring Commands

**Watch nginx error log:**
```bash
sudo tail -f /var/log/nginx/procurement-error.log
```

**Watch Laravel log:**
```bash
tail -f storage/logs/laravel.log
```

**Monitor PHP-FPM:**
```bash
sudo systemctl status php8.2-fpm
```

**Check cache size:**
```bash
du -sh storage/framework/cache/
```

## Support Checklist

Before asking for help, verify:

- [ ] Ran `php artisan cache:clear`
- [ ] Ran `php artisan config:clear`
- [ ] Restarted nginx/Apache
- [ ] Restarted PHP-FPM (if applicable)
- [ ] Ran `php test_timeout_fix.php`
- [ ] Checked nginx error logs
- [ ] Checked Laravel error logs
- [ ] Verified database connection works
- [ ] Tested cache functionality
- [ ] Confirmed timeout settings in nginx config
- [ ] Confirmed timeout settings in PHP config

## Contact Information

For detailed troubleshooting: See `TIMEOUT_FIX_GUIDE.md`
For testing: Run `php test_timeout_fix.php`
For cache clearing: Run `php clear_cache.php`

---

**Last Updated:** 2025-09-30
**Version:** 1.0
**Status:** Production Ready
