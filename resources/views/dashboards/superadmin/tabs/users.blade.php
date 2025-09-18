<!-- Enhanced User Management -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h6>
                <div class="d-flex gap-2 align-items-center">
                    <input type="text" id="user-search" class="form-control form-control-sm" 
                           placeholder="Search users..." style="width: 200px;">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.index') }}">
                        <i class="fas fa-list me-1"></i>View All
                    </a>
                    <a class="btn btn-sm btn-primary" href="{{ route('admin.users.create') }}">
                        <i class="fas fa-plus me-1"></i>Add User
                    </a>
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
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user-plus me-2"></i>Quick User Actions</h6>
            </div>
            <div class="card-body">
                <form id="quick-user-form" data-validate>
                    @csrf
                    <input type="hidden" name="action" value="create_user" />
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input class="form-control form-control-sm" name="username" required />
                        <div class="form-text">Unique username for login</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input class="form-control form-control-sm" name="name" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control form-control-sm" name="email" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select form-select-sm" name="role" required>
                            <option value="">Select Role</option>
                            <option value="requestor">Requestor</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control form-control-sm" name="password" required minlength="6" />
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-plus me-1"></i>Create User
                    </button>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Role Distribution</h6>
            </div>
            <div class="card-body">
                @php
                    $stats = $userStats ?? [];
                    $onlyTwo = ['requestor','superadmin'];
                    $displayStats = [];
                    foreach ($onlyTwo as $r) { $displayStats[$r] = (int)($stats[$r] ?? 0); }
                    if (array_sum($displayStats) === 0 && \Illuminate\Support\Facades\Schema::hasTable('users')) {
                        try {
                            $displayStats = \Illuminate\Support\Facades\DB::table('users')
                                ->select('role', \Illuminate\Support\Facades\DB::raw('COUNT(*) as c'))
                                ->whereIn('role', $onlyTwo)
                                ->groupBy('role')
                                ->pluck('c','role')->toArray();
                            foreach ($onlyTwo as $r) { $displayStats[$r] = (int)($displayStats[$r] ?? 0); }
                        } catch (\Throwable $e) { $displayStats = ['requestor'=>0,'superadmin'=>0]; }
                    }
                @endphp
                @foreach($displayStats as $role => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $role) }}</span>
                        <span class="badge bg-primary">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
