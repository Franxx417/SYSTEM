<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StatusController extends Controller
{
    /**
     * Display a listing of statuses
     */
    public function index(Request $request)
    {
        try {
            if (!Schema::hasTable('statuses')) {
                return response()->json(['error' => 'Statuses table not found'], 404);
            }

            $statuses = DB::table('statuses')
                ->orderBy('status_name')
                ->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $statuses
                ]);
            }

            return view('status.index', compact('statuses'));
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Failed to load statuses: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a newly created status
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'status_name' => 'required|string|max:50|unique:statuses,status_name',
                'description' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:7' // For hex color codes
            ]);

            $statusId = (string) Str::uuid();

            DB::table('statuses')->insert([
                'status_id' => $statusId,
                'status_name' => $request->status_name,
                'description' => $request->description,
                'color' => $request->color ?? '#007bff',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status created successfully',
                    'status_id' => $statusId
                ]);
            }

            return back()->with('success', 'Status created successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Failed to create status: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified status
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'status_name' => 'required|string|max:50|unique:statuses,status_name,' . $id . ',status_id',
                'description' => 'nullable|string|max:255',
                'color' => 'nullable|string|max:7'
            ]);

            $updated = DB::table('statuses')
                ->where('status_id', $id)
                ->update([
                    'status_name' => $request->status_name,
                    'description' => $request->description,
                    'color' => $request->color ?? '#007bff',
                    'updated_at' => now()
                ]);

            if (!$updated) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Status not found'], 404);
                }
                return back()->withErrors(['error' => 'Status not found']);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully'
                ]);
            }

            return back()->with('success', 'Status updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified status
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Check if status is in use
            if (Schema::hasTable('purchase_orders')) {
                $inUse = DB::table('purchase_orders')->where('status', function($query) use ($id) {
                    $query->select('status_name')
                          ->from('statuses')
                          ->where('status_id', $id);
                })->exists();

                if ($inUse) {
                    if ($request->expectsJson()) {
                        return response()->json(['error' => 'Cannot delete status that is currently in use'], 400);
                    }
                    return back()->withErrors(['error' => 'Cannot delete status that is currently in use']);
                }
            }

            $deleted = DB::table('statuses')->where('status_id', $id)->delete();

            if (!$deleted) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Status not found'], 404);
                }
                return back()->withErrors(['error' => 'Status not found']);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status deleted successfully'
                ]);
            }

            return back()->with('success', 'Status deleted successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Failed to delete status: ' . $e->getMessage()]);
        }
    }

    /**
     * Get all statuses for API
     */
    public function getAllStatuses(Request $request)
    {
        try {
            $statuses = DB::table('statuses')
                ->select('status_id', 'status_name', 'description', 'color')
                ->orderBy('status_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $statuses
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin interface for advanced status management
     */
    public function adminIndex(Request $request)
    {
        // Check if user has admin access
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['superadmin', 'authorized_personnel'], true)) {
            abort(403, 'Unauthorized access to admin status management');
        }

        try {
            if (!Schema::hasTable('statuses')) {
                return view('admin.status.index', [
                    'statuses' => collect(),
                    'error' => 'Statuses table not found'
                ]);
            }

            $statuses = DB::table('statuses')
                ->orderBy('status_name')
                ->get();

            // Get usage statistics for each status
            $statusUsage = [];
            if (Schema::hasTable('approvals')) {
                $usage = DB::table('approvals')
                    ->join('statuses', 'statuses.status_id', '=', 'approvals.status_id')
                    ->select('statuses.status_id', 'statuses.status_name', DB::raw('COUNT(*) as usage_count'))
                    ->groupBy('statuses.status_id', 'statuses.status_name')
                    ->get();
                
                foreach ($usage as $u) {
                    $statusUsage[$u->status_id] = $u->usage_count;
                }
            }

            return view('admin.status.index', compact('statuses', 'statusUsage', 'auth'));
        } catch (\Exception $e) {
            return view('admin.status.index', [
                'statuses' => collect(),
                'statusUsage' => [],
                'error' => 'Failed to load statuses: ' . $e->getMessage(),
                'auth' => $auth
            ]);
        }
    }

    /**
     * Show the form for creating a new status
     */
    public function create(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['superadmin', 'authorized_personnel'], true)) {
            abort(403);
        }

        return view('admin.status.create', compact('auth'));
    }

    /**
     * Show the form for editing a status
     */
    public function edit(Request $request, $id)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['superadmin', 'authorized_personnel'], true)) {
            abort(403);
        }

        try {
            $status = DB::table('statuses')->where('status_id', $id)->first();
            if (!$status) {
                return redirect()->route('admin.status.index')->with('error', 'Status not found');
            }

            return view('admin.status.edit', compact('status', 'auth'));
        } catch (\Exception $e) {
            return redirect()->route('admin.status.index')->with('error', 'Failed to load status: ' . $e->getMessage());
        }
    }

    /**
     * Handle status reordering
     */
    public function reorder(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['superadmin', 'authorized_personnel'], true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'order' => 'required|array',
                'order.*' => 'required|string'
            ]);

            // Update the order of statuses
            foreach ($request->order as $index => $statusId) {
                DB::table('statuses')
                    ->where('status_id', $statusId)
                    ->update(['sort_order' => $index + 1]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status order updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get status configuration for API/AJAX requests
     */
    public function config(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['superadmin', 'authorized_personnel'], true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            if (!Schema::hasTable('statuses')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Statuses table not found'
                ]);
            }

            $statuses = DB::table('statuses')
                ->select('status_id', 'status_name', 'description', 'color', 'sort_order')
                ->orderBy('sort_order', 'asc')
                ->orderBy('status_name', 'asc')
                ->get();

            // Get usage statistics
            $statusUsage = [];
            if (Schema::hasTable('approvals')) {
                $usage = DB::table('approvals')
                    ->join('statuses', 'statuses.status_id', '=', 'approvals.status_id')
                    ->select('statuses.status_id', DB::raw('COUNT(*) as usage_count'))
                    ->groupBy('statuses.status_id')
                    ->get();
                
                foreach ($usage as $u) {
                    $statusUsage[$u->status_id] = $u->usage_count;
                }
            }

            // Format the config data
            $config = [
                'statuses' => $statuses->map(function($status) use ($statusUsage) {
                    return [
                        'id' => $status->status_id,
                        'name' => $status->status_name,
                        'description' => $status->description,
                        'color' => $status->color ?? '#6c757d',
                        'sort_order' => $status->sort_order ?? 0,
                        'usage_count' => $statusUsage[$status->status_id] ?? 0,
                        'can_delete' => ($statusUsage[$status->status_id] ?? 0) == 0
                    ];
                }),
                'total_count' => $statuses->count(),
                'total_usage' => array_sum($statusUsage)
            ];

            return response()->json([
                'success' => true,
                'config' => $config
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status configuration
     */
    /**
     * Reset status configuration to default values
     */
    public function reset(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['superadmin', 'authorized_personnel'], true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Default statuses
            $defaultStatuses = [
                ['status_name' => 'Pending', 'description' => 'Awaiting approval', 'color' => '#FFC107', 'sort_order' => 1],
                ['status_name' => 'Approved', 'description' => 'Purchase order approved', 'color' => '#28A745', 'sort_order' => 2],
                ['status_name' => 'Rejected', 'description' => 'Purchase order rejected', 'color' => '#DC3545', 'sort_order' => 3],
                ['status_name' => 'In Progress', 'description' => 'Order is being processed', 'color' => '#17A2B8', 'sort_order' => 4],
                ['status_name' => 'Completed', 'description' => 'Order has been fulfilled', 'color' => '#007BFF', 'sort_order' => 5],
                ['status_name' => 'Cancelled', 'description' => 'Order has been cancelled', 'color' => '#6C757D', 'sort_order' => 6]
            ];

            // Begin transaction
            DB::beginTransaction();

            // Get current statuses in use
            $statusesInUse = [];
            if (Schema::hasTable('purchase_orders')) {
                $statusesInUse = DB::table('purchase_orders')
                    ->select('status')
                    ->distinct()
                    ->pluck('status')
                    ->toArray();
            }

            // Clear existing statuses that are not in use
            DB::table('statuses')
                ->whereNotIn('status_name', $statusesInUse)
                ->delete();

            // Add or update default statuses
            foreach ($defaultStatuses as $status) {
                $existingStatus = DB::table('statuses')
                    ->where('status_name', $status['status_name'])
                    ->first();

                if ($existingStatus) {
                    // Update existing status
                    DB::table('statuses')
                        ->where('status_id', $existingStatus->status_id)
                        ->update([
                            'description' => $status['description'],
                            'color' => $status['color'],
                            'sort_order' => $status['sort_order'],
                            'updated_at' => now()
                        ]);
                } else {
                    // Create new status
                    DB::table('statuses')->insert([
                        'status_id' => (string) Str::uuid(),
                        'status_name' => $status['status_name'],
                        'description' => $status['description'],
                        'color' => $status['color'],
                        'sort_order' => $status['sort_order'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status configuration reset to defaults successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateConfig(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['superadmin', 'authorized_personnel'], true)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $request->validate([
                'action' => 'required|string|in:update_color,update_order,bulk_update',
                'status_id' => 'required_if:action,update_color|string',
                'color' => 'required_if:action,update_color|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'order' => 'required_if:action,update_order|array',
                'statuses' => 'required_if:action,bulk_update|array'
            ]);

            switch ($request->action) {
                case 'update_color':
                    DB::table('statuses')
                        ->where('status_id', $request->status_id)
                        ->update(['color' => $request->color]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Status color updated successfully'
                    ]);

                case 'update_order':
                    foreach ($request->order as $index => $statusId) {
                        DB::table('statuses')
                            ->where('status_id', $statusId)
                            ->update(['sort_order' => $index + 1]);
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Status order updated successfully'
                    ]);

                case 'bulk_update':
                    foreach ($request->statuses as $statusData) {
                        if (isset($statusData['id'])) {
                            $updateData = [];
                            if (isset($statusData['color'])) {
                                $updateData['color'] = $statusData['color'];
                            }
                            if (isset($statusData['description'])) {
                                $updateData['description'] = $statusData['description'];
                            }
                            if (isset($statusData['sort_order'])) {
                                $updateData['sort_order'] = $statusData['sort_order'];
                            }
                            
                            if (!empty($updateData)) {
                                DB::table('statuses')
                                    ->where('status_id', $statusData['id'])
                                    ->update($updateData);
                            }
                        }
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Status configuration updated successfully'
                    ]);

                default:
                    return response()->json([
                        'success' => false,
                        'error' => 'Invalid action'
                    ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
