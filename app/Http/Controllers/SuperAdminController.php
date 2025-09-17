<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class SuperAdminController extends Controller
{
    /**
     * Require superadmin role for all actions in this controller.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $auth = $request->session()->get('auth_user');
            if (!$auth || $auth['role'] !== 'superadmin') {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $settings = [];
        if (Schema::hasTable('settings')) {
            try {
                $settings = DB::table('settings')->pluck('value', 'key');
            } catch (\Throwable $e) {
                $settings = [];
            }
        }

        // Metrics and lists
        $metrics = [
            'total_pos' => (int) DB::table('purchase_orders')->count(),
            'pending_pos' => (int) DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->where('st.status_name', 'Pending')
                ->count(),
            'suppliers' => (int) DB::table('suppliers')->count(),
            'users' => (int) DB::table('users')->count(),
        ];

        $recentPOs = DB::table('purchase_orders')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $suppliers = DB::table('suppliers')->orderBy('name')->limit(10)->get();
        
        // Get statuses for management
        $statuses = DB::table('statuses')->orderBy('status_name')->get();

        return view('dashboards.superadmin', compact('settings', 'metrics', 'recentPOs', 'suppliers', 'statuses'));
    }

    public function updateBranding(Request $request)
    {
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
        $action = (string) $request->input('action');
        switch ($action) {
            case 'cache_clear':
                Artisan::call('optimize:clear');
                return back()->with('status','Caches cleared');
            case 'backup_full':
                try {
                    $tables = $this->allowedTables();
                    $dump = [];
                    foreach ($tables as $t) {
                        $dump[$t] = DB::table($t)->get();
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
    public function getDatabaseInfo()
    {
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
    public function showDatabaseSettings()
    {
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
    public function showLogs()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            $logs = [];
            
            if (file_exists($logPath)) {
                $content = file_get_contents($logPath);
                $lines = array_reverse(explode("\n", $content));
                $logs = array_slice($lines, 0, 100); // Last 100 lines
            }
            
            return view('admin.logs', compact('logs'));
        } catch (\Throwable $e) {
            return back()->withErrors(['logs' => 'Failed to read logs: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Clear system logs
     */
    public function clearLogs()
    {
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
}


