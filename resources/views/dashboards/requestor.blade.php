@extends('layouts.app')
@section('title','Requestor Dashboard')
@section('page_heading','Procurement Dashboard')
@section('page_subheading','Quick overview of your operations')
@section('content')
    {{-- Bootstrap CSS & JS are loaded in layouts/app.blade.php --}}
    <link rel="stylesheet" href="{{ route('dynamic.status.css') }}">
        <div id="req-dashboard" data-summary-url="{{ route('api.dashboard.summary') }}" data-po-show-template="{{ route('po.show', '__po__') }}">
        <!-- Summary cards: quick metrics for the current requestor -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">My POs</div>
                    <div class="h3 mb-0" id="metric-my-total">{{ $metrics['my_total'] ?? 0 }}</div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">Verified</div>
                    <div class="h3 mb-0" id="metric-my-verified">{{ $metrics['my_verified'] ?? 0 }}</div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">Approved</div>
                    <div class="h3 mb-0" id="metric-my-approved">{{ $metrics['my_approved'] ?? 0 }}</div>
                </div></div>
            </div>
        </div>

        <!-- Recent POs -->
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card"><div class="card-header">Recent POs</div>
                    <ul class="list-group list-group-flush">
                        @foreach($recentPOs as $p)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div><strong>#{{ $p->purchase_order_no }}</strong></div>
                                    <small class="text-muted">{{ Str::limit($p->purpose, 30) }}</small>
                                    @if($p->supplier_name)
                                        <br><small class="text-muted">{{ Str::limit($p->supplier_name, 25) }}</small>
                                    @endif
                                </div>
                                <span class="badge bg-primary">₱{{ number_format($p->total,2) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <a class="btn btn-primary w-100 mt-3" href="{{ route('po.create') }}?new=1">Create Purchase Order</a>
            </div>
        </div>
        @vite(['resources/js/dashboards/requestor-dashboard.js'])
@endsection


