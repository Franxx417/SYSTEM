@extends('layouts.app')
@section('title','Dashboard')
@section('page_heading','Dashboard')
@section('page_subheading','System administration and oversight')

@push('styles')
    <link rel="stylesheet" href="/css/superadmin-dashboard.css">
@endpush

@section('content')
<div class="superadmin-dashboard">

<!-- System Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">System Status</div>
                        <div class="h5 mb-0 text-success">Online</div>
                    </div>
                    <div class="status-indicator status-online pulse"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Users</div>
                        <div class="h5 mb-0">{{ $metrics['users'] ?? 0 }}</div>
                    </div>
                    <i class="fas fa-users text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Active Sessions</div>
                        <div class="h5 mb-0">{{ $metrics['active_sessions'] ?? 0 }}</div>
                    </div>
                    <i class="fas fa-user-clock text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Database Size</div>
                        <div class="h5 mb-0">{{ $metrics['db_size'] ?? 'N/A' }}</div>
                    </div>
                    <i class="fas fa-database text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@php($active = request()->get('tab','overview'))
<ul class="nav nav-pills mb-4">
    <li class="nav-item"><a class="nav-link {{ $active==='overview' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'overview']) }}"><i class="fas fa-tachometer-alt me-1"></i>Overview</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='users' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'users']) }}"><i class="fas fa-users me-1"></i>User Management</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='security' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'security']) }}"><i class="fas fa-shield-alt me-1"></i>Security</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='system' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'system']) }}"><i class="fas fa-cogs me-1"></i>System</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='database' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'database']) }}"><i class="fas fa-database me-1"></i>Database</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='logs' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'logs']) }}"><i class="fas fa-file-alt me-1"></i>Logs</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='branding' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'branding']) }}"><i class="fas fa-palette me-1"></i>Branding</a></li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    @include('dashboards.superadmin.tabs.' . $active, ['active' => $active])
</div>

</div>

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/superadmin-dashboard-enhanced.js"></script>
@endpush
@endsection
