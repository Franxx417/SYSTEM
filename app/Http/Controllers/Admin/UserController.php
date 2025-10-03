<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Schema\Blueprint;

class UserController extends Controller
{
    /**
     * Show user management page
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        try {
            $users = DB::table('users')
                ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
                ->leftJoin('role_types', 'role_types.role_type_id', '=', 'roles.role_type_id')
                ->select('users.*', 'role_types.user_role_type as role')
                ->get();

            return view('admin.users.index', compact('users'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to load users']);
        }
    }


    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'username' => 'required|string|max:255|unique:login,username',
                'password' => 'required|string|min:6|confirmed',
                'position' => 'nullable|string|max:255',
                'department' => 'nullable|string|max:255',
                'role' => 'required|in:requestor,authorized_personnel,superadmin',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate UUID for user
            $userId = (string) \Illuminate\Support\Str::uuid();

            // Create user record (password is stored in login table, not users table)
            DB::table('users')->insert([
                'user_id' => $userId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'position' => $validated['position'],
                'department' => $validated['department'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create login credentials (if separate login table is used)
            if (Schema::hasTable('login')) {
                DB::table('login')->insert([
                    'user_id' => $userId,
                    'username' => $validated['username'],
                    'password' => Hash::make($validated['password']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Assign role (if separate roles table is used)
            if (Schema::hasTable('role_types') && Schema::hasTable('roles')) {
                $roleTypeId = DB::table('role_types')
                    ->where('user_role_type', $validated['role'])
                    ->value('role_type_id');

                if ($roleTypeId) {
                    DB::table('roles')->insert([
                        'user_id' => $userId,
                        'role_type_id' => $roleTypeId,
                    ]);
                } else {
                    // Role type not found, log warning but continue
                    Log::warning('Role type not found in role_types table', [
                        'role' => $validated['role'],
                        'available_roles' => DB::table('role_types')->pluck('user_role_type')->toArray()
                    ]);
                    
                    // Create the role type if it doesn't exist
                    $roleTypeId = DB::table('role_types')->insertGetId([
                        'user_role_type' => $validated['role'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Now insert the role
                    DB::table('roles')->insert([
                        'user_id' => $userId,
                        'role_type_id' => $roleTypeId,
                    ]);
                    
                    Log::info('Created missing role type', [
                        'role' => $validated['role'],
                        'role_type_id' => $roleTypeId
                    ]);
                }
            }

            DB::commit();

            // Log the user creation
            Log::info('User created successfully', [
                'user_id' => $userId,
                'username' => $validated['username'],
                'role' => $validated['role'],
                'created_by' => $request->session()->get('auth_user.username')
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully',
                    'user_id' => $userId
                ]);
            }

            return redirect()->route('admin.users.index')->with('status', 'User created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Toggle user active status (activate/deactivate)
     */
    public function toggleActive(Request $request, string $userId)
    {
        $this->authorizeAdmin($request);

        Log::info('Toggle user active request', [
            'user_id' => $userId,
            'request_data' => $request->all(),
            'session_user' => $request->session()->get('auth_user')
        ]);

        try {
            // Ensure users.is_active exists; create if missing and seed existing rows to active
            if (!Schema::hasColumn('users', 'is_active')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true);
                });
                // Seed all users to active to avoid null states
                DB::table('users')->update(['is_active' => true]);
            }

            $user = DB::table('users')->where('user_id', $userId)->first();
            if (!$user) {
                return response()->json(['success' => false, 'error' => 'User not found'], 404);
            }

            // Get current status, defaulting to true if null
            $current = isset($user->is_active) ? (bool)$user->is_active : true;
            
            // Allow client to request explicit state via `active` param; default is toggle
            $requested = $request->has('active') ? (bool) $request->input('active') : !$current;

            // Prevent deactivating the last active superadmin
            if (!$requested) {
                $userRole = DB::table('users')
                    ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
                    ->leftJoin('role_types', 'role_types.role_type_id', '=', 'roles.role_type_id')
                    ->where('users.user_id', $userId)
                    ->value('role_types.user_role_type');

                if ($userRole === 'superadmin') {
                    $activeSuperadmins = DB::table('users')
                        ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
                        ->leftJoin('role_types', 'role_types.role_type_id', '=', 'roles.role_type_id')
                        ->where('role_types.user_role_type', 'superadmin')
                        ->where(function($query) {
                            $query->where('users.is_active', true)
                                  ->orWhereNull('users.is_active');
                        })
                        ->count();

                    if ($activeSuperadmins <= 1) {
                        return response()->json([
                            'success' => false, 
                            'error' => 'Cannot deactivate the last active superadmin'
                        ], 403);
                    }
                }
            }

            DB::table('users')->where('user_id', $userId)->update([
                'is_active' => $requested,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'active' => $requested,
                'message' => $requested ? 'User activated successfully' : 'User deactivated successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('Toggle user active failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to update status: ' . $e->getMessage()], 500);
        }
    }

    private function authorizeAdmin(Request $request): void
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['authorized_personnel','superadmin'], true)) {
            abort(403);
        }
    }
}




