@extends('layouts.app')
@section('title', 'Create User')
@section('page_heading', 'Create User')
@section('page_subheading', 'Add a new user to the system')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create New User</h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username *</label>
                            <input type="text" class="form-control" name="username" value="{{ old('username') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" name="position" value="{{ old('position') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" value="{{ old('department') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Role *</label>
                            <select class="form-select" name="role" required>
                                <option value="">Select Role</option>
                                <option value="requestor" {{ old('role') === 'requestor' ? 'selected' : '' }}>Requestor</option>
                                <option value="authorized_personnel" {{ old('role') === 'authorized_personnel' ? 'selected' : '' }}>Authorized Personnel</option>
                                <option value="superadmin" {{ old('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create User
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Users
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
