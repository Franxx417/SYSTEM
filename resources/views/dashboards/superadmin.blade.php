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
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">System Status</div>
                        <div class="h3 mb-0 text-success">Online</div>
                    </div>
                    <i class="fas fa-server text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Users</div>
                        <div class="h3 mb-0">{{ $metrics['users'] ?? 0 }}</div>
                    </div>
                    <i class="fas fa-users text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Active Sessions</div>
                        <div class="h3 mb-0">{{ $metrics['active_sessions'] ?? 0 }}</div>
                    </div>
                    <i class="fas fa-user-clock text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Database Size</div>
                        <div class="h3 mb-0">{{ $metrics['db_size'] ?? 'N/A' }}</div>
                    </div>
                    <i class="fas fa-database text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@php($active = request()->get('tab','overview'))
@php($tabMap = [
    'purchase-orders' => 'pos',
    'user-management' => 'users',
    'role-management' => 'roles',
    'security' => 'security',
    'system' => 'system',
    'database' => 'database',
    'logs' => 'logs',
    'branding' => 'branding',
    'overview' => 'overview'
])
@php($tabFile = $tabMap[$active] ?? 'overview')

<!-- Tab Content -->
<div class="tab-content">
    @include('dashboards.superadmin.tabs.' . $tabFile, ['active' => $active])
</div>

</div>

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/js/superadmin-dashboard-enhanced.js"></script>
@endpush
@endsection
