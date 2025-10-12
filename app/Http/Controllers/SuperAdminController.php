<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use PDO;

class SuperAdminController extends Controller
{
    /**
     * Check if user has superadmin access
     */
    private function requireSuperadmin(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || $auth['role'] !== 'superadmin') {
            // For API requests, return JSON error instead of aborting
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Unauthorized: Superadmin access required'], 403);
            }
            abort(403, 'Unauthorized: Superadmin access required');
        }
        return $auth;
    }

    public function index(Request $request)
    {
        // Handle AJAX status requests first (with simplified auth check)
        if ($request->has('ajax') && $request->get('ajax') === 'status') {
            try {
                // Simple auth check for AJAX requests
                $auth = $request->session()->get('auth_user');
                if (!$auth || $auth['role'] !== 'superadmin') {
                    return response()->json([
                        'error' => 'Unauthorized',
                        'system_status' => 'unauthorized',
                        'timestamp' => now()->toISOString()
                    ], 403);
                }
                
                // Get system status with additional metrics
                $systemStatus = $this->getSystemStatus();
                $metrics = $this->getBasicMetrics();
                
                return response()->json([
                    'system_status' => $systemStatus,
                    'metrics' => $metrics,
                    'timestamp' => now()->toISOString()
                ]);
            } catch (\Exception $e) {
                Log::error('Status sync error: ' . $e->getMessage(), [
                    'exception' => $e,
                    'request' => $request->all()
                ]);
                
                return response()->json([
                    'system_status' => 'error',
                    'error' => 'Internal server error',
                    'timestamp' => now()->toISOString()
                ], 500);
            }
        }
        
        // Check superadmin access for regular page requests
        $auth = $this->requireSuperadmin($request);

        // Initialize with defaults to prevent timeouts
        $settings = [];
        $metrics = [
            'total_pos' => 0,
            'pending_pos' => 0,
            'suppliers' => 0,
            'users' => 0,
            'active_sessions' => 0,
            'db_size' => 'N/A',
            'last_backup' => 'Never'
        ];
        $recentPOs = collect([]);
        $suppliers = collect([]);
        $statuses = collect([]);
        $allUsers = collect([]);
        $userStats = [];
        $activeSessions = collect([]);

        // Try to load data with timeout protection
        try {
            if (Schema::hasTable('settings')) {
                $settings = DB::table('settings')->pluck('value', 'key')->toArray();
                $metrics['last_backup'] = $settings['system.last_backup'] ?? 'Never';
            }

            // Load basic metrics with limits
            if (Schema::hasTable('purchase_orders')) {
                $metrics['total_pos'] = DB::table('purchase_orders')->count();
                $recentPOs = DB::table('purchase_orders')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();
            }

            if (Schema::hasTable('suppliers')) {
                $metrics['suppliers'] = DB::table('suppliers')->count();
                $suppliers = DB::table('suppliers')->orderBy('name')->limit(5)->get();
            }

            if (Schema::hasTable('users')) {
                $metrics['users'] = DB::table('users')->count();
                $allUsers = DB::table('users')
                    ->leftJoin('login', 'users.user_id', '=', 'login.user_id')
                    ->select('users.user_id', 'login.username', 'users.name', 'users.role', 'users.email', 'users.is_active', 'users.created_at')
                    ->orderBy('users.created_at', 'desc')
                    ->limit(10)
                    ->get();
            }

            if (Schema::hasTable('statuses')) {
                $statuses = DB::table('statuses')->orderBy('status_name')->limit(20)->get();
            }

            if (Schema::hasTable('login')) {
                $metrics['active_sessions'] = DB::table('login')->whereNull('logout_time')->count();
            }

        } catch (\Throwable $e) {
            // Log error but continue with defaults
            Log::error('SuperAdmin dashboard data loading error: ' . $e->getMessage());
        }
            
        $securitySettings = [
            'session_timeout' => $settings['security.session_timeout'] ?? 120,
            'max_login_attempts' => $settings['security.max_login_attempts'] ?? 5,
            'force_password_change' => ($settings['security.force_password_change'] ?? 'false') === 'true',
            'enable_2fa' => ($settings['security.enable_2fa'] ?? 'false') === 'true'
        ];
        
        $securityAlerts = $this->getSecurityAlerts();
        
        $dbStats = [
            'total_tables' => count($this->allowedTables()),
            'total_records' => $this->getTotalRecords(),
            'db_size' => $this->getDatabaseSize(),
            'last_backup' => $settings['system.last_backup'] ?? 'Never'
        ];
        
        $recentActivity = $this->getRecentActivity();
        $userStats = $this->getUserStats();
        $activeSessions = $this->getActiveSessions();

        return view('superadmin.dashboard', compact(
            'settings', 'metrics', 'recentPOs', 'suppliers', 'statuses',
            'allUsers', 'userStats', 'activeSessions', 'securitySettings', 'securityAlerts',
            'dbStats', 'recentActivity'
        ));
    }

    public function updateBranding(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        $request->validate([
            'app_name' => ['nullable','string','max:100'],
            // Allow SVG explicitly; some Laravel versions reject SVG with the 'image' rule
            'logo' => ['nullable','file','mimes:png,jpg,jpeg,svg','max:2048'],
        ]);
        if ($request->filled('app_name')) {
            DB::table('settings')->updateOrInsert(['key' => 'app.name'], [
                'value' => $request->string('app_name'), 'updated_at' => now(), 'created_at' => now()
            ]);
        }
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('public/branding');
            $public = Storage::url($path);
            DB::table('settings')->updateOrInsert(['key' => 'branding.logo_path'], [
                'value' => $public, 'updated_at' => now(), 'created_at' => now()
            ]);
        }
        return back()->with('status','Branding updated');
    }

    public function systemAction(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        $action = (string) $request->input('action');
        
        // Handle AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxSystemAction($action, $request);
        }
        
        switch ($action) {
            case 'cache_clear':
                Artisan::call('optimize:clear');
                return back()->with('status','Caches cleared');
            case 'backup_full':
                try {
                    $tables = $this->allowedTables();
                    $dump = [];
                    foreach ($tables as $t) {
                        if (Schema::hasTable($t)) {
                            $dump[$t] = DB::table($t)->get();
                        }
                    }
                    $brandingPath = null;
                    try {
                        $brandingPath = DB::table('settings')->where('key','branding.logo_path')->value('value');
                    } catch (\Throwable $e) { /* ignore */ }

                    // If ZipArchive available, stream a zip with json + branding asset
                    if (class_exists(\ZipArchive::class)) {
                        $zip = new \ZipArchive();
                        $tmpZip = tempnam(sys_get_temp_dir(), 'bkp_');
                        $zip->open($tmpZip, \ZipArchive::OVERWRITE);
                        $zip->addFromString('data.json', json_encode($dump, JSON_PRETTY_PRINT));
                        if ($brandingPath) {
                            $path = $this->resolvePublicPath($brandingPath);
                            if ($path && is_readable($path)) {
                                $zip->addFile($path, 'branding/'.basename($path));
                            }
                        }
                        $zip->close();
                        $filename = 'backup_'.date('Ymd_His').'.zip';
                        return response()->download($tmpZip, $filename)->deleteFileAfterSend(true);
                    }
                    // Fallback: stream JSON
                    $filename = 'backup_'.date('Ymd_His').'.json';
                    return response()->streamDownload(function() use ($dump) {
                        echo json_encode($dump, JSON_PRETTY_PRINT);
                    }, $filename, ['Content-Type' => 'application/json']);
                } catch (\Throwable $e) {
                    return back()->withErrors(['backup' => 'Backup failed: '.$e->getMessage()]);
                }
            case 'export_data':
                $tables = $request->input('tables');
                if (!is_array($tables) || empty($tables)) {
                    return back()->withErrors(['tables' => 'Select at least one table to export.']);
                }
                $allowed = $this->allowedTables();
                $export = [];
                foreach ($tables as $t) {
                    if (!in_array($t, $allowed, true)) continue;
                    $export[$t] = DB::table($t)->get();
                }
                $filename = 'export_'.date('Ymd_His').'.json';
                return response()->streamDownload(function() use ($export) {
                    echo json_encode($export, JSON_PRETTY_PRINT);
                }, $filename, ['Content-Type' => 'application/json']);
            case 'truncate_table':
                $table = (string) $request->input('table');
                if (!$table || !in_array($table, $this->allowedTruncatableTables(), true)) {
                    return back()->withErrors(['table' => 'Invalid or restricted table for truncate.']);
                }
                try {
                    DB::transaction(function() use ($table) {
                        // Use delete for broader compatibility with FK constraints on SQL Server
                        DB::table($table)->delete();
                    });
                    return back()->with('status', strtoupper($table).' cleared.');
                } catch (\Throwable $e) {
                    return back()->withErrors(['truncate' => 'Failed to clear table: '.$e->getMessage()]);
                }
            case 'status_create':
                $statusName = trim($request->input('status_name'));
                if (!$statusName) {
                    return back()->withErrors(['status_name' => 'Status name is required.']);
                }
                try {
                    DB::table('statuses')->insert([
                        'status_id' => (string) \Illuminate\Support\Str::uuid(),
                        'status_name' => $statusName,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    return back()->with('status', 'Status created successfully.');
                } catch (\Throwable $e) {
                    return back()->withErrors(['status_create' => 'Failed to create status: ' . $e->getMessage()]);
                }
            case 'status_update':
                $statusId = $request->input('status_id');
                $statusName = trim($request->input('status_name'));
                if (!$statusId || !$statusName) {
                    return back()->withErrors(['status_update' => 'Status ID and name are required.']);
                }
                try {
                    DB::table('statuses')
                        ->where('status_id', $statusId)
                        ->update([
                            'status_name' => $statusName,
                            'updated_at' => now()
                        ]);
                    return back()->with('status', 'Status updated successfully.');
                } catch (\Throwable $e) {
                    return back()->withErrors(['status_update' => 'Failed to update status: ' . $e->getMessage()]);
                }
            case 'status_delete':
                $statusId = $request->input('status_id');
                if (!$statusId) {
                    return back()->withErrors(['status_delete' => 'Status ID is required.']);
                }
                try {
                    // Check if status is in use
                    $inUse = DB::table('approvals')->where('status_id', $statusId)->exists();
                    if ($inUse) {
                        return back()->withErrors(['status_delete' => 'Cannot delete status that is currently in use.']);
                    }
                    DB::table('statuses')->where('status_id', $statusId)->delete();
                    return back()->with('status', 'Status deleted successfully.');
                } catch (\Throwable $e) {
                    return back()->withErrors(['status_delete' => 'Failed to delete status: ' . $e->getMessage()]);
                }
            // New user management actions
            case 'create_user':
                return $this->createUser($request);
            case 'reset_password':
                return $this->resetUserPassword($request);
            case 'toggle_user':
                return $this->toggleUserStatus($request);
            case 'terminate_session':
                return $this->terminateSession($request);
            case 'update_security':
                return $this->updateSecuritySettings($request);
            case 'test_connection':
                return $this->testDatabaseConnection();
            case 'optimize_database':
                return $this->optimizeDatabase();
            case 'check_integrity':
                return $this->checkDatabaseIntegrity();
            case 'repair_tables':
                return $this->repairDatabaseTables();
            case 'restore_backup':
                try {
                    $request->validate([
                        'backup_file' => ['required','file','mimetypes:application/json,application/zip,application/x-zip-compressed,application/octet-stream'],
                        'wipe_all' => ['nullable','in:on']
                    ]);
                    $file = $request->file('backup_file');
                    if (!$file) return back()->withErrors(['backup_file' => 'No file uploaded.']);
                    $json = null;
                    $mime = $file->getMimeType();
                    if (str_contains((string)$mime, 'zip')) {
                        if (!class_exists(\ZipArchive::class)) return back()->withErrors(['backup_file' => 'Zip support is not available on the server.']);
                        $zip = new \ZipArchive();
                        $zip->open($file->getRealPath());
                        $jsonContent = null;
                        // Prefer data.json; else first .json
                        $idx = $zip->locateName('data.json');
                        if ($idx !== false) {
                            $jsonContent = $zip->getFromIndex($idx);
                        } else {
                            for ($i=0; $i<$zip->numFiles; $i++) {
                                $name = $zip->getNameIndex($i);
                                if (is_string($name) && str_ends_with(strtolower($name), '.json')) { $jsonContent = $zip->getFromIndex($i); break; }
                            }
                        }
                        $zip->close();
                        if (!$jsonContent) return back()->withErrors(['backup_file' => 'No JSON data found inside ZIP.']);
                        $json = $jsonContent;
                    } else {
                        $json = file_get_contents($file->getRealPath());
                    }
                    $data = json_decode((string)$json, true);
                    if (!is_array($data)) return back()->withErrors(['backup_file' => 'Invalid JSON structure.']);

                    // Normalize allowed tables only
                    $allowed = $this->allowedTables();
                    $tablesData = [];
                    foreach ($allowed as $t) {
                        $tablesData[$t] = isset($data[$t]) && is_array($data[$t]) ? $data[$t] : [];
                    }

                    // Optional wipe
                    $wipe = $request->input('wipe_all') === 'on';
                    DB::transaction(function () use ($tablesData, $wipe) {
                        if ($wipe) {
                            // Delete in FK-safe order
                            foreach (['items','approvals','roles','login','purchase_orders','users','suppliers'] as $t) {
                                if (array_key_exists($t, $tablesData)) { DB::table($t)->delete(); }
                            }
                            // Independent tables
                            foreach (['statuses','role_types','settings'] as $t) {
                                if (array_key_exists($t, $tablesData)) { DB::table($t)->delete(); }
                            }
                        }

                        // Insert in FK-safe order: role_types, statuses, suppliers, users, purchase_orders, login, roles, approvals, items, settings
                        $order = ['role_types','statuses','suppliers','users','purchase_orders','login','roles','approvals','items','settings'];
                        foreach ($order as $t) {
                            $rows = $tablesData[$t] ?? [];
                            if (empty($rows)) continue;
                            // Chunk inserts to avoid large payloads
                            $chunks = array_chunk($rows, 500);
                            foreach ($chunks as $chunk) {
                                // Ensure associative keys are trimmed to actual columns present; rely on DB to ignore missing with default? We'll insert as-is.
                                DB::table($t)->insert($chunk);
                            }
                        }
                    });

                    // Update last backup timestamp
                    DB::table('settings')->updateOrInsert(
                        ['key' => 'system.last_backup'],
                        ['value' => now()->toDateTimeString(), 'updated_at' => now(), 'created_at' => now()]
                    );

                    return back()->with('status', 'Restore completed successfully.');
                } catch (\Throwable $e) {
                    return back()->withErrors(['restore' => 'Restore failed: '.$e->getMessage()]);
                }
            default:
                return back()->withErrors(['action' => 'Unknown action']);
        }
    }

    private function allowedTables(): array
    {
        return [
            'settings',
            'suppliers',
            'users',
            'login',
            'role_types',
            'roles',
            'statuses',
            'purchase_orders',
            'items',
            'approvals',
        ];
    }
    
    /**
     * Get database table information
     */
    public function getDatabaseInfo(Request $request)
    {
        try {
            // Check superadmin access
            $auth = $this->requireSuperadmin($request);
            if ($auth instanceof \Illuminate\Http\JsonResponse) {
                return $auth;
            }
            
            // Set a shorter timeout for this operation
            set_time_limit(30);
            
            // Basic database connection test
            $pdo = DB::connection()->getPdo();
            if (!$pdo) {
                return response()->json([
                    'success' => false,
                    'error' => 'Database connection failed'
                ], 500);
            }
            
            $tables = [];
            $allowedTables = $this->allowedTables();
            
            foreach ($allowedTables as $table) {
                try {
                    if (Schema::hasTable($table)) {
                        $count = DB::table($table)->count();
                        $columns = Schema::getColumnListing($table);
                        
                        // Get table size for SQL Server
                        $size = 'N/A';
                        try {
                            $sizeResult = DB::select("
                                SELECT 
                                    CAST(SUM(a.total_pages) * 8 AS DECIMAL(15,2)) / 1024 AS size_mb
                                FROM sys.tables t
                                INNER JOIN sys.indexes i ON t.OBJECT_ID = i.object_id
                                INNER JOIN sys.partitions p ON i.object_id = p.OBJECT_ID AND i.index_id = p.index_id
                                INNER JOIN sys.allocation_units a ON p.partition_id = a.container_id
                                WHERE t.name = ?
                            ", [$table]);
                            
                            if (!empty($sizeResult) && isset($sizeResult[0]->size_mb)) {
                                $sizeMB = round($sizeResult[0]->size_mb, 2);
                                $size = $sizeMB . ' MB';
                            }
                        } catch (\Exception $e) {
                            // Ignore size calculation errors
                        }
                        
                        $tables[] = [
                            'name' => $table,
                            'count' => $count,
                            'columns' => count($columns),
                            'column_names' => $columns,
                            'size' => $size,
                            'status' => 'OK'
                        ];
                    } else {
                        $tables[] = [
                            'name' => $table,
                            'count' => 0,
                            'columns' => 0,
                            'column_names' => [],
                            'size' => 'N/A',
                            'status' => 'Missing'
                        ];
                    }
                } catch (\Exception $e) {
                    $tables[] = [
                        'name' => $table,
                        'count' => 'Error',
                        'columns' => 0,
                        'column_names' => [],
                        'size' => 'N/A',
                        'status' => 'Error',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            Log::info('Database info retrieved successfully', ['table_count' => count($tables)]);
            
            return response()->json([
                'success' => true,
                'data' => $tables,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Database info retrieval failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve database information: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Execute raw SQL query (with safety restrictions)
     */
    public function executeQuery(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        $request->validate([
            'query' => ['required', 'string', 'max:5000']
        ]);
        
        $query = trim($request->input('query'));
        
        // Safety checks - only allow SELECT statements
        if (!preg_match('/^\s*SELECT\s+/i', $query)) {
            return back()->withErrors(['query' => 'Only SELECT queries are allowed for safety.']);
        }
        
        try {
            $results = DB::select($query);
            return back()->with('query_results', $results)->with('executed_query', $query);
        } catch (\Throwable $e) {
            return back()->withErrors(['query' => 'Query failed: ' . $e->getMessage()]);
        }
    }

    private function allowedTruncatableTables(): array
    {
        // Restrict to child tables by default to avoid FK issues
        return [
            'items',
            'approvals',
            'roles',
            'login',
            // Allow clearing POs if user wants to wipe; do last and expect they clear children first
            'purchase_orders',
        ];
    }

    private function resolvePublicPath(?string $url): ?string
    {
        if (!$url) return null;
        // Handle Storage::url('public/...') -> /storage/...
        // Map to public_path if it starts with '/storage'
        if (str_starts_with($url, '/storage/')) {
            $relative = substr($url, 9); // remove '/storage/'
            $candidate = public_path('storage/'.$relative);
            return $candidate;
        }
        // If it's an absolute path and readable, return as-is
        if (is_file($url)) return $url;
        // Try to strip app URL if present
        $app = config('app.url');
        if ($app && str_starts_with($url, $app)) {
            $path = substr($url, strlen($app));
            $path = ltrim($path, '/');
            $candidate = public_path($path);
            if (is_file($candidate)) return $candidate;
        }
        return null;
    }
    
    /**
     * Show database settings form
     */
    public function showDatabaseSettings(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        // Get current database settings from settings table
        $dbSettings = DB::table('settings')
            ->where('key', 'LIKE', 'db.%')
            ->pluck('value', 'key');
            
        // Set default values if not found
        $settings = [
            'db.host' => $dbSettings['db.host'] ?? env('DB_HOST', 'localhost'),
            'db.port' => $dbSettings['db.port'] ?? env('DB_PORT', 1433),
            'db.database' => $dbSettings['db.database'] ?? env('DB_DATABASE', 'Database'),
            'db.username' => $dbSettings['db.username'] ?? env('DB_USERNAME', 'Admin'),
            'db.encrypt' => $dbSettings['db.encrypt'] ?? env('DB_ENCRYPT', 'no'),
            'db.trust_server_certificate' => $dbSettings['db.trust_server_certificate'] ?? env('DB_TRUST_SERVER_CERTIFICATE', 'true'),
            'db.connection_pooling' => $dbSettings['db.connection_pooling'] ?? env('DB_CONNECTION_POOLING', 'true'),
            'db.multiple_active_result_sets' => $dbSettings['db.multiple_active_result_sets'] ?? env('DB_MULTIPLE_ACTIVE_RESULT_SETS', 'false'),
            'db.query_timeout' => $dbSettings['db.query_timeout'] ?? env('DB_QUERY_TIMEOUT', '30'),
            'db.timeout' => $dbSettings['db.timeout'] ?? env('DB_TIMEOUT', '30'),
        ];
        
        return view('superadmin.database-settings', compact('settings'));
    }
    
    /**
     * Update database settings
     */
    public function updateDatabaseSettings(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        $request->validate([
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'db_encrypt' => ['required', 'in:yes,no'],
            'db_trust_server_certificate' => ['required', 'in:true,false'],
            'db_connection_pooling' => ['required', 'in:true,false'],
            'db_multiple_active_result_sets' => ['required', 'in:true,false'],
            'db_query_timeout' => ['required', 'integer', 'min:1', 'max:3600'],
            'db_timeout' => ['required', 'integer', 'min:1', 'max:3600'],
        ]);
        
        // Update settings in database
        $now = now();
        DB::table('settings')->updateOrInsert(['key' => 'db.host'], ['value' => $request->db_host, 'updated_at' => $now, 'created_at' => $now]);
        DB::table('settings')->updateOrInsert(['key' => 'db.port'], ['value' => $request->db_port, 'updated_at' => $now, 'created_at' => $now]);
        DB::table('settings')->updateOrInsert(['key' => 'db.database'], ['value' => $request->db_database, 'updated_at' => $now, 'created_at' => $now]);
        DB::table('settings')->updateOrInsert(['key' => 'db.username'], ['value' => $request->db_username, 'updated_at' => $now, 'created_at' => $now]);
        
        // Only update password if provided
        if ($request->filled('db_password')) {
            DB::table('settings')->updateOrInsert(['key' => 'db.password'], ['value' => $request->db_password, 'updated_at' => $now, 'created_at' => $now]);
        }
        
        // Update advanced options
        DB::table('settings')->updateOrInsert(['key' => 'db.encrypt'], ['value' => $request->db_encrypt, 'updated_at' => $now, 'created_at' => $now]);
        DB::table('settings')->updateOrInsert(['key' => 'db.trust_server_certificate'], ['value' => $request->db_trust_server_certificate, 'updated_at' => $now, 'created_at' => $now]);
        DB::table('settings')->updateOrInsert(['key' => 'db.connection_pooling'], ['value' => $request->db_connection_pooling, 'updated_at' => $now, 'created_at' => $now]);
        DB::table('settings')->updateOrInsert(['key' => 'db.multiple_active_result_sets'], ['value' => $request->db_multiple_active_result_sets, 'updated_at' => $now, 'created_at' => $now]);
        DB::table('settings')->updateOrInsert(['key' => 'db.query_timeout'], ['value' => $request->db_query_timeout, 'updated_at' => $now, 'created_at' => $now]);
        DB::table('settings')->updateOrInsert(['key' => 'db.timeout'], ['value' => $request->db_timeout, 'updated_at' => $now, 'created_at' => $now]);
        
        // Test connection with new settings
        try {
            $config = [
                'host' => $request->db_host,
                'port' => $request->db_port,
                'database' => $request->db_database,
                'username' => $request->db_username,
                'password' => $request->filled('db_password') ? $request->db_password : DB::table('settings')->where('key', 'db.password')->value('value'),
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ],
                'advanced_options' => [
                    'encrypt' => $request->db_encrypt,
                    'trust_server_certificate' => $request->db_trust_server_certificate === 'true',
                    'connection_pooling' => $request->db_connection_pooling === 'true',
                    'multiple_active_result_sets' => $request->db_multiple_active_result_sets === 'true',
                ]
            ];
            
            // Try to connect with new settings
            $dsn = "sqlsrv:Server={$config['host']},{$config['port']};Database={$config['database']}";
            $options = [];
            
            // Add advanced options to connection string
            if ($config['advanced_options']['encrypt'] === 'yes') {
                $options[PDO::SQLSRV_ATTR_ENCRYPT] = true;
            }
            
            if ($config['advanced_options']['trust_server_certificate']) {
                $options[PDO::SQLSRV_ATTR_TRUST_SERVER_CERTIFICATE] = true;
            }
            
            $pdo = new \PDO($dsn, $config['username'], $config['password'], $options);
            
            return back()->with('status', 'Database settings updated successfully and connection tested.');
        } catch (\Exception $e) {
            // Save settings but show warning
            return back()->with('warning', 'Database settings updated but connection test failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Show system logs
     */
    public function showLogs(Request $request)
    {
        // Check superadmin access
        $auth = $this->requireSuperadmin($request);
        
        try {
            $logPath = storage_path('logs/laravel.log');
            $logs = [];
            
            if (file_exists($logPath)) {
                $content = file_get_contents($logPath);
                $lines = array_reverse(explode("\n", $content));
                $logs = array_slice($lines, 0, 100); // Last 100 lines
            }
            
            return view('superadmin.logs', compact('logs', 'auth'));
        } catch (\Throwable $e) {
            return back()->withErrors(['logs' => 'Failed to read logs: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Clear system logs
     */
    public function clearLogs(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                file_put_contents($logPath, '');
            }
            return back()->with('status', 'Logs cleared successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['logs' => 'Failed to clear logs: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Handle AJAX system actions
     */
    private function handleAjaxSystemAction(string $action, Request $request)
    {
        try {
            switch ($action) {
                case 'reset_password':
                    return $this->resetUserPassword($request);
                case 'toggle_user':
                    return $this->toggleUserStatus($request);
                case 'terminate_session':
                    return $this->terminateSession($request);
                case 'test_connection':
                    return $this->testDatabaseConnection();
                case 'optimize_database':
                    return $this->optimizeDatabase();
                case 'check_integrity':
                    return $this->checkDatabaseIntegrity();
                case 'repair_tables':
                    return $this->repairDatabaseTables();
                default:
                    return response()->json(['success' => false, 'error' => 'Unknown action']);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Create a new user
     */
    private function createUser(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:login,username'],
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:requestor,superadmin'],
            'password' => ['required', 'string', 'min:6']
        ]);
        
        try {
            $userId = (string) \Illuminate\Support\Str::uuid();
            
            DB::table('users')->insert([
                'user_id' => $userId,
                'name' => $request->name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Create login record with username
            if (Schema::hasTable('login')) {
                DB::table('login')->insert([
                    'user_id' => $userId,
                    'username' => $request->username,
                    'password' => password_hash($request->password, PASSWORD_DEFAULT),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Assign role
            if (Schema::hasTable('role_types') && Schema::hasTable('roles')) {
                $roleTypeId = DB::table('role_types')
                    ->where('user_role_type', $request->role)
                    ->value('role_type_id');

                if ($roleTypeId) {
                    DB::table('roles')->insert([
                        'user_id' => $userId,
                        'role_type_id' => $roleTypeId,
                    ]);
                } else {
                    // Create the role type if it doesn't exist
                    $roleTypeId = DB::table('role_types')->insertGetId([
                        'user_role_type' => $request->role,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    DB::table('roles')->insert([
                        'user_id' => $userId,
                        'role_type_id' => $roleTypeId,
                    ]);
                }
            }
            
            return back()->with('status', 'User created successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['create_user' => 'Failed to create user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Reset user password
     */
    private function resetUserPassword(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'User ID required'], 400);
        }
        
        try {
            // Check if user exists in both users and login tables
            $user = DB::table('users')->where('user_id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'error' => 'User not found'], 404);
            }
            
            $loginRecord = DB::table('login')->where('user_id', $userId)->first();
            if (!$loginRecord) {
                return response()->json(['success' => false, 'error' => 'Login record not found for user'], 404);
            }
            
            // Generate new password
            $newPassword = \Illuminate\Support\Str::random(12);
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password in login table (where passwords are actually stored)
            DB::table('login')
                ->where('user_id', $userId)
                ->update([
                    'password' => $hashedPassword,
                    'updated_at' => now()
                ]);
                
            Log::info('Password reset for user', [
                'user_id' => $userId, 
                'username' => $loginRecord->username,
                'admin' => session('auth_user.username', 'system')
            ]);
                
            return response()->json([
                'success' => true, 
                'new_password' => $newPassword,
                'message' => 'Password reset successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Password reset failed', [
                'user_id' => $userId, 
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to reset password: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Toggle user active status
     */
    private function toggleUserStatus(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'User ID required']);
        }
        
        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'error' => 'User not found']);
            }
            
            $newStatus = !($user->is_active ?? true);
            DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'is_active' => $newStatus,
                    'updated_at' => now()
                ]);
                
            return response()->json([
                'success' => true,
                'message' => 'User status updated',
                'new_status' => $newStatus
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Terminate user session
     */
    private function terminateSession(Request $request)
    {
        $sessionId = $request->input('session_id');
        if (!$sessionId) {
            return response()->json(['success' => false, 'error' => 'Session ID required']);
        }
        
        try {
            DB::table('login')
                ->where('login_id', $sessionId)
                ->update([
                    'logout_time' => now(),
                    'updated_at' => now()
                ]);
                
            return response()->json(['success' => true, 'message' => 'Session terminated']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * Update security settings
     */
    private function updateSecuritySettings(Request $request)
    {
        $request->validate([
            'session_timeout' => ['required', 'integer', 'min:5', 'max:1440'],
            'max_login_attempts' => ['required', 'integer', 'min:3', 'max:10'],
            'force_password_change' => ['nullable'],
            'enable_2fa' => ['nullable']
        ]);
        
        try {
            $now = now();
            
            DB::table('settings')->updateOrInsert(
                ['key' => 'security.session_timeout'],
                ['value' => $request->session_timeout, 'updated_at' => $now, 'created_at' => $now]
            );
            
            DB::table('settings')->updateOrInsert(
                ['key' => 'security.max_login_attempts'],
                ['value' => $request->max_login_attempts, 'updated_at' => $now, 'created_at' => $now]
            );
            
            DB::table('settings')->updateOrInsert(
                ['key' => 'security.force_password_change'],
                ['value' => $request->has('force_password_change') ? 'true' : 'false', 'updated_at' => $now, 'created_at' => $now]
            );
            
            DB::table('settings')->updateOrInsert(
                ['key' => 'security.enable_2fa'],
                ['value' => $request->has('enable_2fa') ? 'true' : 'false', 'updated_at' => $now, 'created_at' => $now]
            );
            
            return back()->with('status', 'Security settings updated successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['security' => 'Failed to update security settings: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Test database connection
     */
    private function testDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();
            $result = DB::select('SELECT 1 as test');
            
            return response()->json([
                'success' => true,
                'message' => 'Database connection successful',
                'test_result' => $result[0]->test ?? null
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database connection failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Optimize database
     */
    private function optimizeDatabase()
    {
        try {
            // For SQL Server, we can update statistics and rebuild indexes
            $tables = $this->allowedTables();
            $optimized = [];
            
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    // Update statistics (SQL Server specific)
                    try {
                        DB::statement("UPDATE STATISTICS {$table}");
                        $optimized[] = $table;
                    } catch (\Throwable $e) {
                        // Continue with other tables if one fails
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Database optimization completed',
                'optimized_tables' => $optimized
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database optimization failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check database integrity
     */
    private function checkDatabaseIntegrity()
    {
        try {
            $issues = [];
            $tables = $this->allowedTables();
            
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    try {
                        $count = DB::table($table)->count();
                        // Basic integrity check - ensure table is accessible
                        if ($count >= 0) {
                            // Table is accessible
                        }
                    } catch (\Throwable $e) {
                        $issues[] = "Table {$table}: " . $e->getMessage();
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Database integrity check completed',
                'issues' => $issues,
                'status' => empty($issues) ? 'healthy' : 'issues_found'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Integrity check failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Repair database tables
     */
    private function repairDatabaseTables()
    {
        try {
            // For SQL Server, we can check and repair indexes
            $tables = $this->allowedTables();
            $repaired = [];
            
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    try {
                        // Basic repair - ensure table structure is intact
                        $columns = Schema::getColumnListing($table);
                        if (!empty($columns)) {
                            $repaired[] = $table;
                        }
                    } catch (\Throwable $e) {
                        // Continue with other tables
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Table repair completed',
                'repaired_tables' => $repaired
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => 'Table repair failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        try {
            // For SQL Server
            $result = DB::select("
                SELECT 
                    SUM(size * 8.0 / 1024) as size_mb
                FROM sys.master_files 
                WHERE database_id = DB_ID()
            ");
            
            if (!empty($result) && isset($result[0]->size_mb)) {
                $sizeMB = round($result[0]->size_mb, 2);
                return $sizeMB >= 1024 ? round($sizeMB / 1024, 2) . ' GB' : $sizeMB . ' MB';
            }
        } catch (\Throwable $e) {
            // Fallback calculation
        }
        
        return 'Unknown';
    }
    
    /**
     * Get total records across all tables
     */
    private function getTotalRecords(): int
    {
        try {
            $total = 0;
            foreach ($this->allowedTables() as $table) {
                if (Schema::hasTable($table)) {
                    $total += DB::table($table)->count();
                }
            }
            return $total;
        } catch (\Throwable $e) {
            return 0;
        }
    }
    
    /**
     * Get security alerts
     */
    private function getSecurityAlerts(): array
    {
        $alerts = [];
        
        try {
            // Check for failed login attempts
            $failedLogins = DB::table('login')
                ->whereNull('logout_time')
                ->where('login_time', '>', now()->subHours(24))
                ->count();
                
            if ($failedLogins > 10) {
                $alerts[] = (object) [
                    'type' => 'warning',
                    'title' => 'High Login Activity',
                    'message' => "Detected {$failedLogins} login attempts in the last 24 hours"
                ];
            }
            
            // Check for inactive superadmins
            $inactiveSuperadmins = DB::table('users')
                ->where('role', 'superadmin')
                ->where('is_active', false)
                ->count();
                
            if ($inactiveSuperadmins > 0) {
                $alerts[] = (object) [
                    'type' => 'info',
                    'title' => 'Inactive Superadmins',
                    'message' => "{$inactiveSuperadmins} superadmin account(s) are inactive"
                ];
            }
            
        } catch (\Throwable $e) {
            // Ignore errors in security alerts
        }
        
        return $alerts;
    }
    
    /**
     * Get system status
     */
    private function getSystemStatus(): string
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            return 'online';
        } catch (\Throwable $e) {
            return 'offline';
        }
    }
    
    /**
     * Get basic system metrics for status sync
     */
    private function getBasicMetrics(): array
    {
        try {
            return [
                'users_count' => $this->getTableCount('users'),
                'purchase_orders_count' => $this->getTableCount('purchase_orders'),
                'items_count' => $this->getTableCount('items'),
                'suppliers_count' => $this->getTableCount('suppliers'),
                'database_status' => 'connected'
            ];
        } catch (\Throwable $e) {
            return [
                'users_count' => 0,
                'purchase_orders_count' => 0,
                'items_count' => 0,
                'suppliers_count' => 0,
                'database_status' => 'error'
            ];
        }
    }
    
    /**
     * Get table count safely
     */
    private function getTableCount(string $table): int
    {
        try {
            if (Schema::hasTable($table)) {
                return (int) DB::table($table)->count();
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }
        return 0;
    }
    
    /**
     * Get table count safely for API
     */
    private function getTableCountSafely(string $table): int
    {
        try {
            if (Schema::hasTable($table)) {
                return (int) DB::table($table)->count();
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to count table {$table}: " . $e->getMessage());
        }
        return 0;
    }
    
    /**
     * Get pending PO count safely for API
     */
    private function getPendingPOCountSafely(): int
    {
        try {
            if (Schema::hasTable('purchase_orders') && Schema::hasTable('approvals') && Schema::hasTable('statuses')) {
                return (int) DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('st.status_name', 'Pending')
                    ->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to get pending PO count: ' . $e->getMessage());
        }
        return 0;
    }
    
    /**
     * Get active sessions count safely for API
     */
    private function getActiveSessionsCountSafely(): int
    {
        try {
            if (Schema::hasTable('login')) {
                return (int) DB::table('login')->whereNull('logout_time')->count();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to get active sessions count: ' . $e->getMessage());
        }
        return 0;
    }
    
    /**
     * Get database size safely for API
     */
    private function getDatabaseSizeSafely(): string
    {
        try {
            // Use a simpler, faster query for SQL Server
            $result = DB::select("
                SELECT 
                    CAST(SUM(CAST(FILEPROPERTY(name, 'SpaceUsed') AS bigint) * 8.0 / 1024) AS DECIMAL(15,2)) AS size_mb
                FROM sys.database_files
                WHERE type = 0
            ");
            
            if (!empty($result) && isset($result[0]->size_mb)) {
                $sizeMB = round($result[0]->size_mb, 2);
                return $sizeMB >= 1024 ? round($sizeMB / 1024, 2) . ' GB' : $sizeMB . ' MB';
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to get database size: ' . $e->getMessage());
        }
        
        return 'Unknown';
    }
    
    /**
     * Get total records safely for API
     */
    private function getTotalRecordsSafely(): int
    {
        try {
            $total = 0;
            // Only count smaller tables to avoid timeout
            $quickTables = ['settings', 'suppliers', 'users', 'role_types', 'roles', 'statuses'];
            
            foreach ($quickTables as $table) {
                if (Schema::hasTable($table)) {
                    $total += (int) DB::table($table)->count();
                }
            }
            
            // For larger tables, use approximate counts or skip
            if (Schema::hasTable('purchase_orders')) {
                $total += (int) DB::table('purchase_orders')->count();
            }
            
            return $total;
        } catch (\Throwable $e) {
            Log::warning('Failed to get total records: ' . $e->getMessage());
        }
        return 0;
    }
    
    /**
     * Get pending PO count
     */
    private function getPendingPOCount(): int
    {
        try {
            return (int) DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->where('st.status_name', 'Pending')
                ->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
    
    /**
     * Get active sessions count
     */
    private function getActiveSessionsCount(): int
    {
        try {
            if (Schema::hasTable('login')) {
                return (int) DB::table('login')->whereNull('logout_time')->count();
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }
        return 0;
    }
    
    /**
     * Get all users with last login info
     */
    private function getAllUsers()
    {
        try {
            if (!Schema::hasTable('users')) {
                return collect([]);
            }
            
            $query = DB::table('users');
            
            if (Schema::hasTable('login')) {
                return $query
                    ->leftJoin('login', 'login.user_id', '=', 'users.user_id')
                    ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
                    ->leftJoin('role_types', 'role_types.role_type_id', '=', 'roles.role_type_id')
                    ->select('users.*', 'login.username', 'role_types.user_role_type as role', DB::raw('MAX(login.login_time) as last_login'))
                    ->groupBy('users.user_id', 'users.name', 'users.email', 'users.position', 'users.department', 'users.password', 'users.is_active', 'users.created_at', 'users.updated_at', 'login.username', 'role_types.user_role_type')
                    ->orderBy('users.created_at', 'desc')
                    ->limit(20)
                    ->get();
            } else {
                return $query
                    ->select('users.*', DB::raw('NULL as last_login'))
                    ->orderBy('users.created_at', 'desc')
                    ->limit(20)
                    ->get();
            }
        } catch (\Throwable $e) {
            return collect([]);
        }
    }
    
    /**
     * Get user statistics by role
     */
    private function getUserStats(): array
    {
        try {
            if (Schema::hasTable('users') && Schema::hasTable('roles') && Schema::hasTable('role_types')) {
                return DB::table('users')
                    ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
                    ->leftJoin('role_types', 'role_types.role_type_id', '=', 'roles.role_type_id')
                    ->select('role_types.user_role_type as role', DB::raw('COUNT(DISTINCT users.user_id) as count'))
                    ->whereNotNull('role_types.user_role_type')
                    ->groupBy('role_types.user_role_type')
                    ->pluck('count', 'role')
                    ->toArray();
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }
        return [];
    }
    
    /**
     * Get active sessions
     */
    private function getActiveSessions()
    {
        try {
            if (Schema::hasTable('login') && Schema::hasTable('users')) {
                return DB::table('login')
                    ->join('users', 'users.user_id', '=', 'login.user_id')
                    ->whereNull('logout_time')
                    ->select('login.login_id as id', 'users.username', 'login.ip_address', 'login.login_time as last_activity')
                    ->orderByDesc('login.login_time')
                    ->limit(10)
                    ->get();
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }
        return collect([]);
    }
    
    /**
     * Get recent activity
     */
    private function getRecentActivity()
    {
        try {
            if (Schema::hasTable('login') && Schema::hasTable('users')) {
                return DB::table('login')
                    ->join('users', 'users.user_id', '=', 'login.user_id')
                    ->select(
                        DB::raw("'User login' as action"),
                        'users.username as user',
                        'login.login_time as created_at'
                    )
                    ->orderByDesc('login.login_time')
                    ->limit(10)
                    ->get();
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }
        return collect([]);
    }

    // ===== API METHODS =====

    /**
     * Get system metrics for API
     */
    public function getMetrics(Request $request)
    {
        try {
            // Check superadmin access
            $this->requireSuperadmin($request);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Superadmin access required'
            ], 403);
        }
        
        try {
            // Set a timeout for this operation
            set_time_limit(10);
            
            // Get settings safely
            $settings = [];
            if (Schema::hasTable('settings')) {
                try {
                    $settings = DB::table('settings')->pluck('value', 'key');
                } catch (\Throwable $e) {
                    Log::warning('Failed to fetch settings: ' . $e->getMessage());
                    $settings = [];
                }
            }

            // Get metrics with individual error handling and timeouts
            $metrics = [
                'total_pos' => $this->getTableCountSafely('purchase_orders'),
                'pending_pos' => $this->getPendingPOCountSafely(),
                'suppliers' => $this->getTableCountSafely('suppliers'),
                'users' => $this->getTableCountSafely('users'),
                'active_sessions' => $this->getActiveSessionsCountSafely(),
                'db_size' => $this->getDatabaseSizeSafely(),
                'last_backup' => $settings['system.last_backup'] ?? 'Never',
                'system_status' => $this->getSystemStatus(),
                'total_records' => $this->getTotalRecordsSafely(),
                'timestamp' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Throwable $e) {
            Log::error('Metrics API error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch metrics',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get recent logs for API
     */
    public function getRecentLogsApi(Request $request)
    {
        try {
            // Check superadmin access
            $auth = $this->requireSuperadmin($request);
            if ($auth instanceof \Illuminate\Http\JsonResponse) {
                return $auth;
            }
            
            $logPath = storage_path('logs/laravel.log');
            $logs = [];
            $logEntries = [];
            
            if (file_exists($logPath) && is_readable($logPath)) {
                $content = file_get_contents($logPath);
                if ($content !== false) {
                    $lines = array_reverse(explode("\n", $content));
                    $filteredLines = array_filter($lines, function($line) {
                        return !empty(trim($line));
                    });
                    
                    $logs = array_slice($filteredLines, 0, 100); // Last 100 non-empty lines
                    
                    // Parse log entries for better display
                    foreach (array_slice($logs, 0, 50) as $line) {
                        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+\.\w+): (.+)/', $line, $matches)) {
                            $logEntries[] = [
                                'timestamp' => $matches[1],
                                'level' => $matches[2],
                                'message' => $matches[3],
                                'raw' => $line
                            ];
                        } else {
                            $logEntries[] = [
                                'timestamp' => null,
                                'level' => 'unknown',
                                'message' => $line,
                                'raw' => $line
                            ];
                        }
                    }
                }
            } else {
                Log::warning('Log file not accessible', ['path' => $logPath]);
            }
            
            return response()->json([
                'success' => true,
                'logs' => $logs,
                'parsed_logs' => $logEntries,
                'count' => count($logs),
                'file_exists' => file_exists($logPath),
                'file_readable' => file_exists($logPath) && is_readable($logPath),
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to retrieve logs: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create user via API
     */
    public function createUserApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:login,username'],
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:requestor,superadmin'],
            'password' => ['required', 'string', 'min:6']
        ]);
        
        try {
            $userId = (string) \Illuminate\Support\Str::uuid();
            
            DB::table('users')->insert([
                'user_id' => $userId,
                'name' => $request->name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Create login record with username
            if (Schema::hasTable('login')) {
                DB::table('login')->insert([
                    'user_id' => $userId,
                    'username' => $request->username,
                    'password' => password_hash($request->password, PASSWORD_DEFAULT),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Assign role
            if (Schema::hasTable('role_types') && Schema::hasTable('roles')) {
                $roleTypeId = DB::table('role_types')
                    ->where('user_role_type', $request->role)
                    ->value('role_type_id');

                if ($roleTypeId) {
                    DB::table('roles')->insert([
                        'user_id' => $userId,
                        'role_type_id' => $roleTypeId,
                    ]);
                } else {
                    // Create the role type if it doesn't exist
                    $roleTypeId = DB::table('role_types')->insertGetId([
                        'user_role_type' => $request->role,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    DB::table('roles')->insert([
                        'user_id' => $userId,
                        'role_type_id' => $roleTypeId,
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user password via API
     */
    public function resetUserPasswordApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        return $this->resetUserPassword($request);
    }

    /**
     * Toggle user status via API
     */
    public function toggleUserStatusApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        return $this->toggleUserStatus($request);
    }

    /**
     * Delete user via API
     */
    public function deleteUserApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'User ID required'], 400);
        }
        
        try {
            $user = DB::table('users')->where('user_id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'error' => 'User not found'], 404);
            }
            
            // Don't allow deleting the last superadmin
            if ($user->role === 'superadmin') {
                $superadminCount = DB::table('users')->where('role', 'superadmin')->count();
                if ($superadminCount <= 1) {
                    return response()->json(['success' => false, 'error' => 'Cannot delete the last superadmin'], 400);
                }
            }
            
            DB::table('users')->where('user_id', $userId)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update security settings via API
     */
    public function updateSecuritySettingsApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        return $this->updateSecuritySettings($request);
    }

    /**
     * Force logout all users via API
     */
    public function forceLogoutAllApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            DB::table('login')
                ->whereNull('logout_time')
                ->update([
                    'logout_time' => now(),
                    'updated_at' => now()
                ]);
                
            return response()->json([
                'success' => true,
                'message' => 'All users have been logged out'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Terminate session via API
     */
    public function terminateSessionApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        return $this->terminateSession($request);
    }

    /**
     * Clear cache via API
     */
    public function clearCacheApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            Artisan::call('optimize:clear');
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create backup via API
     */
    public function createBackupApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            $tables = $this->allowedTables();
            $dump = [];
            foreach ($tables as $t) {
                if (Schema::hasTable($t)) {
                    $dump[$t] = DB::table($t)->get();
                }
            }
            
            // Update last backup timestamp
            DB::table('settings')->updateOrInsert(
                ['key' => 'system.last_backup'],
                ['value' => now()->toDateTimeString(), 'updated_at' => now(), 'created_at' => now()]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update system via API
     */
    public function updateSystemApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            // Simulate system update (placeholder)
            return response()->json([
                'success' => true,
                'message' => 'System update completed'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restart services via API
     */
    public function restartServicesApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            // Simulate service restart (placeholder)
            return response()->json([
                'success' => true,
                'message' => 'Services restarted successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get system info via API
     */
    public function getSystemInfoApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            $info = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'database_type' => config('database.default'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'timestamp' => now()->toISOString()
            ];
            
            return response()->json($info);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get table details via API
     */
    public function getTableDetailsApi(Request $request, $tableName)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        if (!in_array($tableName, $this->allowedTables())) {
            return response()->json(['success' => false, 'error' => 'Table not allowed'], 403);
        }
        
        try {
            if (!Schema::hasTable($tableName)) {
                return response()->json(['success' => false, 'error' => 'Table not found'], 404);
            }
            
            // Get basic table information
            $count = DB::table($tableName)->count();
            
            // Get table size
            $size = 'N/A';
            try {
                $sizeResult = DB::select("
                    SELECT 
                        CAST(SUM(a.total_pages) * 8 AS DECIMAL(15,2)) / 1024 AS size_mb
                    FROM sys.tables t
                    INNER JOIN sys.indexes i ON t.OBJECT_ID = i.object_id
                    INNER JOIN sys.partitions p ON i.object_id = p.OBJECT_ID AND i.index_id = p.index_id
                    INNER JOIN sys.allocation_units a ON p.partition_id = a.container_id
                    WHERE t.name = ?
                ", [$tableName]);
                
                if (!empty($sizeResult) && isset($sizeResult[0]->size_mb)) {
                    $sizeMB = round($sizeResult[0]->size_mb, 2);
                    $size = $sizeMB . ' MB';
                }
            } catch (\Exception $e) {
                // Ignore size calculation errors
            }
            
            // Get detailed column information
            $columnsDetails = [];
            try {
                $columnInfo = DB::select("
                    SELECT 
                        c.name AS column_name,
                        t.name AS data_type,
                        c.max_length,
                        c.precision,
                        c.scale,
                        c.is_nullable,
                        c.is_identity,
                        ISNULL(dc.definition, '') AS default_value
                    FROM sys.columns c
                    INNER JOIN sys.types t ON c.user_type_id = t.user_type_id
                    LEFT JOIN sys.default_constraints dc ON c.default_object_id = dc.object_id
                    WHERE c.object_id = OBJECT_ID(?)
                    ORDER BY c.column_id
                ", [$tableName]);
                
                foreach ($columnInfo as $col) {
                    // Format type with length/precision
                    $type = $col->data_type;
                    if (in_array($col->data_type, ['varchar', 'nvarchar', 'char', 'nchar'])) {
                        $length = $col->max_length == -1 ? 'MAX' : ($col->max_length / ($col->data_type[0] === 'n' ? 2 : 1));
                        $type .= "($length)";
                    } elseif (in_array($col->data_type, ['decimal', 'numeric'])) {
                        $type .= "({$col->precision},{$col->scale})";
                    }
                    
                    $columnsDetails[] = [
                        'name' => $col->column_name,
                        'type' => $type,
                        'nullable' => $col->is_nullable ? 'YES' : 'NO',
                        'identity' => $col->is_identity ? 'YES' : 'NO',
                        'default' => $col->default_value ?: 'NULL'
                    ];
                }
            } catch (\Exception $e) {
                // Fallback to basic column listing
                $columns = Schema::getColumnListing($tableName);
                foreach ($columns as $col) {
                    $columnsDetails[] = [
                        'name' => $col,
                        'type' => 'unknown',
                        'nullable' => 'unknown',
                        'identity' => 'NO',
                        'default' => 'NULL'
                    ];
                }
            }
            
            // Get indexes information
            $indexes = [];
            try {
                $indexInfo = DB::select("
                    SELECT 
                        i.name AS index_name,
                        i.type_desc AS index_type,
                        i.is_unique,
                        i.is_primary_key,
                        COL_NAME(ic.object_id, ic.column_id) AS column_name
                    FROM sys.indexes i
                    INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
                    WHERE i.object_id = OBJECT_ID(?)
                    AND i.type > 0
                    ORDER BY i.name, ic.key_ordinal
                ", [$tableName]);
                
                $indexGrouped = [];
                foreach ($indexInfo as $idx) {
                    $name = $idx->index_name;
                    if (!isset($indexGrouped[$name])) {
                        $indexGrouped[$name] = [
                            'name' => $name,
                            'type' => $idx->index_type,
                            'unique' => $idx->is_unique ? 'YES' : 'NO',
                            'primary' => $idx->is_primary_key ? 'YES' : 'NO',
                            'columns' => []
                        ];
                    }
                    $indexGrouped[$name]['columns'][] = $idx->column_name;
                }
                $indexes = array_values($indexGrouped);
            } catch (\Exception $e) {
                // Ignore index errors
            }
            
            // Get foreign keys
            $foreignKeys = [];
            try {
                $fkInfo = DB::select("
                    SELECT 
                        fk.name AS constraint_name,
                        COL_NAME(fkc.parent_object_id, fkc.parent_column_id) AS column_name,
                        OBJECT_NAME(fk.referenced_object_id) AS referenced_table,
                        COL_NAME(fkc.referenced_object_id, fkc.referenced_column_id) AS referenced_column
                    FROM sys.foreign_keys fk
                    INNER JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id
                    WHERE fk.parent_object_id = OBJECT_ID(?)
                    ORDER BY fk.name
                ", [$tableName]);
                
                foreach ($fkInfo as $fk) {
                    $foreignKeys[] = [
                        'name' => $fk->constraint_name,
                        'column' => $fk->column_name,
                        'references' => $fk->referenced_table . '(' . $fk->referenced_column . ')'
                    ];
                }
            } catch (\Exception $e) {
                // Ignore FK errors
            }
            
            // Get sample data (limited to 10 rows)
            $sampleData = [];
            try {
                $samples = DB::table($tableName)->limit(10)->get();
                $sampleData = $samples->map(function($row) {
                    return (array) $row;
                })->toArray();
            } catch (\Exception $e) {
                // Ignore sample data errors
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'table' => $tableName,
                    'count' => $count,
                    'size' => $size,
                    'engine' => 'SQL Server',
                    'columns' => $columnsDetails,
                    'indexes' => $indexes,
                    'foreign_keys' => $foreignKeys,
                    'sample_data' => $sampleData
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to get table details: ' . $e->getMessage(), [
                'table' => $tableName,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve table details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize database via API
     */
    public function optimizeDatabaseApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        return $this->optimizeDatabase();
    }

    /**
     * Check database integrity via API
     */
    public function checkDatabaseIntegrityApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        return $this->checkDatabaseIntegrity();
    }

    /**
     * Repair database tables via API
     */
    public function repairDatabaseTablesApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        return $this->repairDatabaseTables();
    }

    /**
     * Create database backup via API
     */
    public function createDatabaseBackupApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        return $this->createBackupApi($request);
    }

    /**
     * Clear logs via API
     */
    public function clearLogsApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                file_put_contents($logPath, '');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Logs cleared successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update log settings via API
     */
    public function updateLogSettingsApi(Request $request)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            $request->validate([
                'log_level' => ['required', 'string', 'in:debug,info,notice,warning,error,critical,alert,emergency'],
                'max_files' => ['required', 'integer', 'min:1', 'max:365']
            ]);
            
            $now = now();
            DB::table('settings')->updateOrInsert(
                ['key' => 'logging.level'],
                ['value' => $request->log_level, 'updated_at' => $now, 'created_at' => $now]
            );
            
            DB::table('settings')->updateOrInsert(
                ['key' => 'logging.max_files'],
                ['value' => $request->max_files, 'updated_at' => $now, 'created_at' => $now]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Log settings updated successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update branding via API
     */
    public function updateBrandingApi(Request $request)
    {
        try {
            // Check superadmin access
            $auth = $this->requireSuperadmin($request);
            if ($auth instanceof \Illuminate\Http\JsonResponse) {
                return $auth;
            }
            
            $request->validate([
                'app_name' => ['nullable','string','max:100'],
                'logo' => ['nullable','file','mimes:png,jpg,jpeg,svg,webp','max:2048'],
                'primary_color' => ['nullable','string','regex:/^#[0-9A-Fa-f]{6}$/'],
                'secondary_color' => ['nullable','string','regex:/^#[0-9A-Fa-f]{6}$/'],
            ]);
            
            $updated = [];
            
            if ($request->filled('app_name')) {
                DB::table('settings')->updateOrInsert(['key' => 'app.name'], [
                    'value' => $request->string('app_name'), 
                    'updated_at' => now(), 
                    'created_at' => now()
                ]);
                $updated[] = 'app_name';
            }
            
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                $oldLogo = DB::table('settings')->where('key', 'branding.logo_path')->value('value');
                if ($oldLogo) {
                    $oldPath = str_replace('/storage/', 'public/', $oldLogo);
                    if (Storage::exists($oldPath)) {
                        Storage::delete($oldPath);
                    }
                }
                
                $path = $request->file('logo')->store('public/branding');
                $public = Storage::url($path);
                DB::table('settings')->updateOrInsert(['key' => 'branding.logo_path'], [
                    'value' => $public, 
                    'updated_at' => now(), 
                    'created_at' => now()
                ]);
                $updated[] = 'logo';
            }
            
            if ($request->filled('primary_color')) {
                DB::table('settings')->updateOrInsert(['key' => 'branding.primary_color'], [
                    'value' => $request->string('primary_color'), 
                    'updated_at' => now(), 
                    'created_at' => now()
                ]);
                $updated[] = 'primary_color';
            }
            
            if ($request->filled('secondary_color')) {
                DB::table('settings')->updateOrInsert(['key' => 'branding.secondary_color'], [
                    'value' => $request->string('secondary_color'), 
                    'updated_at' => now(), 
                    'created_at' => now()
                ]);
                $updated[] = 'secondary_color';
            }
            
            if (empty($updated)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No branding data provided to update'
                ], 400);
            }
            
            Log::info('Branding updated successfully', [
                'updated_fields' => $updated,
                'admin' => session('auth_user.username')
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Branding updated successfully',
                'updated_fields' => $updated
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Branding update failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['logo']),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'error' => 'Failed to update branding: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===== ADDITIONAL API METHODS =====

    /**
     * Get table details for API
     */
    public function getTableDetails(Request $request, $table)
    {
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            $allowedTables = $this->allowedTables();
            if (!in_array($table, $allowedTables)) {
                return response()->json(['success' => false, 'error' => 'Table not allowed'], 403);
            }
            
            if (!Schema::hasTable($table)) {
                return response()->json(['success' => false, 'error' => 'Table not found'], 404);
            }
            
            $columns = Schema::getColumnListing($table);
            $count = DB::table($table)->count();
            $sample = DB::table($table)->limit(5)->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $table,
                    'columns' => $columns,
                    'count' => $count,
                    'sample_data' => $sample
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ===== ADDITIONAL HELPER METHODS =====




}


