@extends('layouts.app')
@section('title', 'Account Settings')
@section('page_heading', 'Account Settings')
@section('page_subheading', 'Manage your account preferences and security')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.profile.update') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $user->name ?? $auth['name'] }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="{{ $user->email ?? $auth['email'] }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" name="position" value="{{ $user->position ?? $auth['position'] }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" value="{{ $user->department ?? $auth['department'] }}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-image me-2"></i>Company Logo</h6>
            </div>
            <div class="card-body">
                @if($currentLogo)
                    <div class="mb-3">
                        <label class="form-label">Current Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $currentLogo }}" alt="Company Logo" class="img-thumbnail" style="max-height: 80px; max-width: 200px;">
                            <form method="POST" action="{{ route('settings.logo.remove') }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to remove the logo?')">
                                    <i class="fas fa-trash me-1"></i>Remove Logo
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('settings.logo.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ $currentLogo ? 'Replace Logo' : 'Upload Logo' }}</label>
                        <input type="file" class="form-control" name="logo" accept="image/*" required>
                        <div class="form-text">
                            Supported formats: JPEG, PNG, GIF, SVG, WebP. Maximum size: 2MB.<br>
                            Recommended dimensions: 300x100px for optimal display on documents.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>{{ $currentLogo ? 'Replace Logo' : 'Upload Logo' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.password.update') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="new_password_confirmation" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-muted">Role</div>
                    <div class="fw-medium">{{ ucwords(str_replace('_', ' ', $auth['role'])) }}</div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">User ID</div>
                    <div class="fw-medium">{{ $auth['user_id'] }}</div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">Last Updated</div>
                    <div class="fw-medium">{{ $user->updated_at ?? 'Never' }}</div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection
