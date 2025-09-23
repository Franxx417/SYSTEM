{{-- Role Management Tab --}}
<div class="row g-3">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-user-shield me-2"></i>Role Management</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    @php($roleStats = [
                        'superadmin' => DB::table('users')->join('roles', 'users.user_id', '=', 'roles.user_id')->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')->where('role_types.user_role_type', 'superadmin')->count(),
                        'authorized_personnel' => DB::table('users')->join('roles', 'users.user_id', '=', 'roles.user_id')->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')->where('role_types.user_role_type', 'authorized_personnel')->count(),
                        'requestor' => DB::table('users')->join('roles', 'users.user_id', '=', 'roles.user_id')->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')->where('role_types.user_role_type', 'requestor')->count()
                    ])
                    <div class="col-md-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small">Super Admins</div>
                                        <div class="h4">{{ $roleStats['superadmin'] }}</div>
                                    </div>
                                    <i class="fas fa-user-crown fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small">Authorized Personnel</div>
                                        <div class="h4">{{ $roleStats['authorized_personnel'] }}</div>
                                    </div>
                                    <i class="fas fa-user-tie fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small">Requestors</div>
                                        <div class="h4">{{ $roleStats['requestor'] }}</div>
                                    </div>
                                    <i class="fas fa-user fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Role Permissions</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Permission</th>
                                                <th class="text-center">Requestor</th>
                                                <th class="text-center">Auth. Personnel</th>
                                                <th class="text-center">Super Admin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Create Purchase Orders</td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                            </tr>
                                            <tr>
                                                <td>Approve Purchase Orders</td>
                                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                            </tr>
                                            <tr>
                                                <td>Manage Users</td>
                                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                            </tr>
                                            <tr>
                                                <td>Manage Suppliers</td>
                                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                            </tr>
                                            <tr>
                                                <td>System Administration</td>
                                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                            </tr>
                                            <tr>
                                                <td>Database Management</td>
                                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                                <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                                <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Recent Role Changes</h6>
                            </div>
                            <div class="card-body">
                                <div class="text-muted text-center py-4">
                                    <i class="fas fa-history fa-2x mb-2 d-block"></i>
                                    Role change tracking not implemented yet
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-users me-1"></i>Manage Users & Roles
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
