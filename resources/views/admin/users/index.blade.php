<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center">
            <h2>Users</h2>
            <div>
                <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Back to Dashboard</a>
                <a class="btn btn-primary ms-2" href="{{ route('admin.users.create') }}">Add User</a>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success mt-3">{{ session('status') }}</div>
        @endif

        <div class="table-responsive mt-3">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->position }}</td>
                        <td>{{ $u->department }}</td>
                        <td>{{ $u->role ?? '-' }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" onclick="editUser('{{ $u->user_id }}', '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ addslashes($u->position) }}', '{{ addslashes($u->department) }}', '{{ $u->role }}')">Edit</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Name</label>
                                <input class="form-control" name="name" id="edit_user_name" required maxlength="255" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Email</label>
                                <input class="form-control" name="email" id="edit_user_email" type="email" required maxlength="255" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input class="form-control" name="position" id="edit_user_position" maxlength="100" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input class="form-control" name="department" id="edit_user_department" maxlength="100" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Role</label>
                                <select class="form-select" name="role" id="edit_user_role">
                                    <option value="">Select Role</option>
                                    <option value="superadmin">Super Admin</option>
                                    <option value="authorized_personnel">Authorized Personnel</option>
                                    <option value="requestor">Requestor</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/js/admin-users-index.js"></script>
</body>
</html>


