<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\ConstantsService;

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
        // Force JSON response for AJAX requests
        $wantsJson = $request->wantsJson() || $request->ajax() || $request->expectsJson();
        
        try {
            // Validate inputs
            $validator = \Validator::make($request->all(), [
                'status_name' => 'required|string|max:50|unique:statuses,status_name',
                'description' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                if ($wantsJson) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $statusId = (string) Str::uuid();

            DB::table('statuses')->insert([
                'status_id' => $statusId,
                'status_name' => trim($request->status_name),
                'description' => trim($request->description ?? ''),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status created successfully',
                    'data' => [
                        'status_id' => $statusId,
                        'status_name' => $request->status_name
                    ]
                ], 201);
            }

            return back()->with('success', 'Status created successfully');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Status creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create status: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to create status: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified status
     */
    public function update(Request $request, $id)
    {
        // Force JSON response for AJAX requests
        $wantsJson = $request->wantsJson() || $request->ajax() || $request->expectsJson();
        
        try {
            // Validate inputs
            $validator = \Validator::make($request->all(), [
                'status_name' => 'required|string|max:50|unique:statuses,status_name,' . $id . ',status_id',
                'description' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                if ($wantsJson) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            // Check if status exists
            $exists = DB::table('statuses')->where('status_id', $id)->exists();
            if (!$exists) {
                if ($wantsJson) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Status not found'
                    ], 404);
                }
                return back()->withErrors(['error' => 'Status not found']);
            }

            // Update the status
            $updated = DB::table('statuses')
                ->where('status_id', $id)
                ->update([
                    'status_name' => trim($request->status_name),
                    'description' => trim($request->description ?? ''),
                    'updated_at' => now()
                ]);

            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully',
                    'data' => [
                        'status_id' => $id,
                        'status_name' => $request->status_name
                    ]
                ], 200);
            }

            return back()->with('success', 'Status updated successfully');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Status update failed', [
                'status_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update status: ' . $e->getMessage()
                ], 500);
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
     * Get status configuration for API/AJAX requests
     */
    public function config(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || $auth['role'] !== 'superadmin') {
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

}
