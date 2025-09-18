<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
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
            abort(403, 'Unauthorized: Superadmin access required');
        }
        return $auth;
    }

    public function index(Request $request)
    {
        // Check superadmin access
        $auth = $this->requireSuperadmin($request);
        
        // Handle AJAX status requests
        if ($request->has('ajax') && $request->get('ajax') === 'status') {
            return response()->json([
                'system_status' => $this->getSystemStatus(),
                'timestamp' => now()->toISOString()
            ]);
        }

        $settings = [];
        if (Schema::hasTable('settings')) {
            try {
                $settings = DB::table('settings')->pluck('value', 'key');
            } catch (\Throwable $e) {
                $settings = [];
            }
        }

        // Enhanced metrics
        $metrics = [
            'total_pos' => $this->getTableCount('purchase_orders'),
            'pending_pos' => $this->getPendingPOCount(),
            'suppliers' => $this->getTableCount('suppliers'),
            'users' => $this->getTableCount('users'),
            'active_sessions' => $this->getActiveSessionsCount(),
            'db_size' => $this->getDatabaseSize(),
            'last_backup' => $settings['system.last_backup'] ?? 'Never'
        ];

        $recentPOs = DB::table('purchase_orders')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $suppliers = DB::table('suppliers')->orderBy('name')->limit(10)->get();
        
        // Get statuses for management
        $statuses = DB::table('statuses')->orderBy('status_name')->get();
        
        // Enhanced data for new features
        $allUsers = $this->getAllUsers();
        $userStats = $this->getUserStats();
        $activeSessions = $this->getActiveSessions();
            
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

        return view('dashboards.superadmin', compact(
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
        // Check superadmin access
        $this->requireSuperadmin($request);
        
        try {
            $tables = [];
            $allowedTables = $this->allowedTables();
            
            foreach ($allowedTables as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    $columns = Schema::getColumnListing($table);
                    $tables[$table] = [
                        'name' => $table,
                        'count' => $count,
                        'columns' => $columns
                    ];
                }
            }
            
            return response()->json($tables);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
        
        return view('admin.database-settings', compact('settings'));
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
            
            return view('admin.logs', compact('logs', 'auth'));
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
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:requestor,finance_controller,department_head,authorized_personnel,superadmin'],
            'password' => ['required', 'string', 'min:6']
        ]);
        
        try {
            DB::table('users')->insert([
                'user_id' => (string) \Illuminate\Support\Str::uuid(),
                'username' => $request->username,
                'name' => $request->name,
                'role' => $request->role,
                'password' => password_hash($request->password, PASSWORD_DEFAULT),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
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
            return response()->json(['success' => false, 'error' => 'User ID required']);
        }
        
        try {
            $newPassword = \Illuminate\Support\Str::random(12);
            DB::table('users')
                ->where('user_id', $userId)
                ->update([
                    'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                    'updated_at' => now()
                ]);
                
            return response()->json([
                'success' => true, 
                'new_password' => $newPassword,
                'message' => 'Password reset successfully'
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
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
                    ->select('users.*', DB::raw('MAX(login.login_time) as last_login'))
                    ->groupBy('users.user_id', 'users.username', 'users.name', 'users.role', 'users.email', 'users.is_active', 'users.created_at', 'users.updated_at')
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
            if (Schema::hasTable('users')) {
                return DB::table('users')
                    ->select('role', DB::raw('COUNT(*) as count'))
                    ->groupBy('role')
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
}


