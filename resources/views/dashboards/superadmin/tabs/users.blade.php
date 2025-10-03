<!-- Enhanced User Management -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h6>
                <div class="d-flex gap-2 align-items-center">
                    <input type="text" id="user-search" class="form-control form-control-sm" 
                           placeholder="Search users..." style="width: 200px;">
                    <button class="btn btn-sm btn-outline-success" onclick="refreshUserList()">
                        <i class="fas fa-sync me-1"></i>Refresh
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="showAddUserModal()">
                        <i class="fas fa-plus me-1"></i>Add User
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @php
                    use Illuminate\Support\Facades\DB;
                    use Illuminate\Support\Facades\Schema;

                    // Fallback: if controller didn't supply $allUsers, fetch here (try to resolve role gracefully)
                    $usersList = collect($allUsers ?? []);
                    if ($usersList->isEmpty()) {
                        try {
                            if (Schema::hasTable('users')) {
                                $hasRoleCol = Schema::hasColumn('users','role');
                                $hasRoleIdCol = Schema::hasColumn('users','role_id');
                                $hasRolesTable = Schema::hasTable('roles') && (Schema::hasColumn('roles','id') || Schema::hasColumn('roles','role_id')) && (Schema::hasColumn('roles','name') || Schema::hasColumn('roles','role_name'));

                                $query = DB::table('users')->select('users.*');
                                if ($hasRoleIdCol && $hasRolesTable) {
                                    // determine key and name columns dynamically
                                    $rolesIdCol = Schema::hasColumn('roles','id') ? 'roles.id' : 'roles.role_id';
                                    $rolesNameCol = Schema::hasColumn('roles','name') ? 'roles.name' : 'roles.role_name';
                                    $query = $query->leftJoin('roles','users.role_id','=', DB::raw(str_replace('roles.','',$rolesIdCol)))
                                                   ->addSelect(DB::raw($rolesNameCol.' as role_name'));
                                }

                                $usersList = $query->orderBy('users.created_at','desc')->limit(100)->get();
                            }
                        } catch (\Throwable $e) { $usersList = collect(); }
                    }

                    // Normalize a display role for each user (support various schemas)
                    $usersList = $usersList->map(function($u){
                        $role = null;
                        $candidates = [
                            $u->role ?? null,
                            $u->role_name ?? null,
                            $u->user_role ?? null,
                            $u->type ?? null,
                            $u->user_type ?? null,
                            $u->account_type ?? null,
                            $u->access_level ?? null,
                        ];
                        foreach ($candidates as $cand) {
                            if (is_string($cand) && trim($cand) !== '') { $role = trim($cand); break; }
                        }
                        // Map numeric role_id if present
                        if (!$role && isset($u->role_id) && is_numeric($u->role_id)) {
                            $role = ((int)$u->role_id === 2) ? 'superadmin' : 'requestor';
                        }
                        // Map numeric role if present
                        if (!$role && isset($u->role) && is_numeric($u->role)) {
                            $role = ((int)$u->role === 2) ? 'superadmin' : 'requestor';
                        }
                        // Heuristic from position/department
                        if (!$role) {
                            $hint = strtolower(($u->position ?? '').' '.($u->department ?? ''));
                            if (str_contains($hint, 'admin') || str_contains($hint, 'it') || str_contains($hint, 'system')) {
                                $role = 'superadmin';
                            } else {
                                $role = 'requestor';
                            }
                        }
                        // Normalize casing
                        $roleNorm = strtolower($role);
                        if (in_array($roleNorm, ['admin','administrator','super admin','super_admin','superadmin','sa'])) {
                            $role = 'superadmin';
                        } elseif (in_array($roleNorm, ['requestor','requester','rq'])) {
                            $role = 'requestor';
                        }
                        $u->display_role = $role;
                        return $u;
                    });
                @endphp
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usersList as $user)
                                <tr class="user-row">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                {{ strtoupper(substr($user->name ?? $user->username ?? 'U', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium user-name">{{ $user->name ?? $user->username }}</div>
                                                <div class="text-muted small user-email">{{ $user->email ?? 'No email' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary user-role">{{ $user->display_role ?? ($user->role ?? 'Unknown') }}</span>
                                    </td>
                                    <td>
                                        @if(($user->is_active ?? true))
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $user->last_login ?? ($user->updated_at ?? 'Never') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-warning" 
                                                    data-action="reset-password" 
                                                    data-user-id="{{ $user->user_id ?? $user->id }}">
                                                <i class="fas fa-key me-1"></i>Reset
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    data-action="toggle-user" 
                                                    data-user-id="{{ $user->user_id ?? $user->id }}">
                                                <i class="fas fa-{{ ($user->is_active ?? true) ? 'ban' : 'check' }} me-1"></i>
                                                {{ ($user->is_active ?? true) ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No users found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <!-- Role Distribution -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Role Distribution</h6>
            </div>
            <div class="card-body">
                @php
                    $stats = $userStats ?? [];
                    $displayStats = [];
                    
                    // If userStats provided by controller, use it
                    if (!empty($stats)) {
                        $displayStats = $stats;
                    } else {
                        // Fallback: calculate from database
                        try {
                            if (\Illuminate\Support\Facades\Schema::hasTable('users') && 
                                \Illuminate\Support\Facades\Schema::hasTable('roles') && 
                                \Illuminate\Support\Facades\Schema::hasTable('role_types')) {
                                $displayStats = \Illuminate\Support\Facades\DB::table('users')
                                    ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
                                    ->leftJoin('role_types', 'role_types.role_type_id', '=', 'roles.role_type_id')
                                    ->select('role_types.user_role_type as role', \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT users.user_id) as count'))
                                    ->whereNotNull('role_types.user_role_type')
                                    ->groupBy('role_types.user_role_type')
                                    ->pluck('count', 'role')
                                    ->toArray();
                            }
                        } catch (\Throwable $e) {}
                    }
                    
                    // Ensure we always show requestor and superadmin
                    if (empty($displayStats)) {
                        $displayStats = ['requestor' => 0, 'superadmin' => 0];
                    }
                @endphp
                @forelse($displayStats as $role => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $role) }}</span>
                        <span class="badge bg-primary">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-muted text-center">No role data available</p>
                @endforelse
            </div>
        </div>

        
    </div>
</div>
