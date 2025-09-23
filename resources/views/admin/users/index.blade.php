@extends('layouts.app')
@section('title', 'User Management')
@section('page_heading', 'User Management')
@section('page_subheading', 'Manage system users and permissions')

@push('styles')
    <style>
        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 0.875rem;
        }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h6>
                </div>
                <button class="btn btn-primary btn-sm" onclick="window.location.href='{{ route('admin.users.create') }}'">
                    <i class="fas fa-plus me-1"></i>Add User
                </button>
            </div>
            <div class="card-body p-0">
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th class="d-none d-md-table-cell">Position</th>
                                <th class="d-none d-lg-table-cell">Department</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($users ?? [] as $u)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $u->name }}</div>
                                            <div class="text-muted small">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell"><span class="text-muted">{{ $u->position ?? 'N/A' }}</span></td>
                                <td class="d-none d-lg-table-cell"><span class="text-muted">{{ $u->department ?? 'N/A' }}</span></td>
                                <td>
                                    @if($u->role === 'superadmin')
                                        <span class="badge bg-danger">{{ ucfirst($u->role) }}</span>
                                    @elseif($u->role === 'authorized_personnel')
                                        <span class="badge bg-warning">Authorized Personnel</span>
                                    @elseif($u->role === 'requestor')
                                        <span class="badge bg-info">{{ ucfirst($u->role) }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $u->role ?? 'Unknown' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php($active = isset($u->is_active) ? (bool)$u->is_active : true)
                                    <span class="badge status-badge {{ $active ? 'bg-success' : 'bg-danger' }}">{{ $active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm d-none d-md-flex">
                                        <button class="btn btn-outline-primary" onclick="editUser('{{ $u->user_id }}', '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ addslashes($u->position) }}', '{{ addslashes($u->department) }}', '{{ $u->role }}')">
                                            <i class="fas fa-edit"></i><span class="ms-1 d-none d-xl-inline">Edit</span>
                                        </button>
                                        <button class="btn btn-outline-warning" 
                                                data-action="reset-password" 
                                                data-user-id="{{ $u->user_id }}"
                                                data-bs-toggle="tooltip" 
                                                title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        @php($active = isset($u->is_active) ? (bool)$u->is_active : true)
                                        <button class="btn toggle-user-btn {{ $active ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                data-action="toggle-user" 
                                                data-user-id="{{ $u->user_id }}" 
                                                data-active="{{ $active ? 1 : 0 }}"
                                                data-bs-toggle="tooltip" 
                                                title="{{ $active ? 'Deactivate User' : 'Activate User' }}">
                                            <i class="fas fa-{{ $active ? 'ban' : 'check' }}"></i><span class="ms-1 d-none d-xl-inline">{{ $active ? 'Deactivate' : 'Activate' }}</span>
                                        </button>
                                    </div>
                                    
                                    <!-- Mobile dropdown menu -->
                                    <div class="dropdown d-md-none">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item" onclick="editUser('{{ $u->user_id }}', '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ addslashes($u->position) }}', '{{ addslashes($u->department) }}', '{{ $u->role }}')">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" 
                                                        data-action="reset-password" 
                                                        data-user-id="{{ $u->user_id }}">
                                                    <i class="fas fa-key me-2"></i>Reset Password
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                @php($active = isset($u->is_active) ? (bool)$u->is_active : true)
                                                <button class="dropdown-item toggle-user-btn {{ $active ? 'text-danger' : 'text-success' }}" 
                                                        data-action="toggle-user" 
                                                        data-user-id="{{ $u->user_id }}" 
                                                        data-active="{{ $active ? 1 : 0 }}">
                                                    <i class="fas fa-{{ $active ? 'ban' : 'check' }} me-2"></i>{{ $active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    No users found
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
            <form id="editUserForm" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" id="edit_position" name="position">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" id="edit_department" name="department">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="requestor">Requestor</option>
                            <option value="authorized_personnel">Authorized Personnel</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="/js/admin-users-index.js"></script>
<script>
function editUser(userId, name, email, position, department, role) {
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_position').value = position || '';
    document.getElementById('edit_department').value = department || '';
    document.getElementById('edit_role').value = role;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function showNotification(message, type = 'success') {
    // Simple notification - can be enhanced
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        <i class="fas ${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endpush
@endsection
