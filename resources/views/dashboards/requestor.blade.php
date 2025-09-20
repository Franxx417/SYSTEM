@extends('layouts.app')
@section('title','Requestor Dashboard')
@section('page_heading','Procurement Dashboard')
@section('page_subheading','Quick overview of your operations')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
        <div id="req-dashboard" data-summary-url="{{ route('api.dashboard.summary') }}" data-po-show-template="{{ route('po.show', '__po__') }}">
        <!-- Summary cards: quick metrics for the current requestor -->
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">My POs</div>
                    <div class="h3 mb-0" id="metric-my-total">{{ $metrics['my_total'] ?? 0 }}</div>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">Drafts</div>
                    <div class="h3 mb-0" id="metric-my-drafts">{{ $metrics['my_drafts'] ?? 0 }}</div>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">Verified</div>
                    <div class="h3 mb-0" id="metric-my-verified">{{ $metrics['my_verified'] ?? 0 }}</div>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">Approved</div>
                    <div class="h3 mb-0" id="metric-my-approved">{{ $metrics['my_approved'] ?? 0 }}</div>
                </div></div>
            </div>
        </div>

        <!-- Tables: drafts and recent POs -->
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card"><div class="card-header">My Draft POs</div>
                    <div class="card-body p-0">
                        <table class="table mb-0" id="table-drafts">
                            <thead><tr><th>No.</th><th>Purpose</th><th>Status</th><th class="text-end">Total</th><th></th></tr></thead>
                            <tbody>
                            @forelse($myDrafts as $r)
                                <tr>
                                  <td>{{ $r->purchase_order_no }}</td>
                                  <td>{{ $r->purpose }}</td>
                                  <td><span class="badge bg-secondary">{{ $r->status_name }}</span></td>
                                  <td class="text-end">{{ number_format($r->total,2) }}</td>
                                  <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('po.show', $r->purchase_order_no) }}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
</svg></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No drafts</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card"><div class="card-header">Recent POs</div>
                    <ul class="list-group list-group-flush">
                        @foreach($recentPOs as $p)
                            <li class="list-group-item d-flex justify-content-between"><span>#{{ $p->purchase_order_no }} - {{ $p->purpose }}</span><span class="fw-semibold">{{ number_format($p->total,2) }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <a class="btn btn-primary w-100 mt-3" href="{{ route('po.create') }}">Create Purchase Order</a>
            </div>
        </div>
        <script src="/js/requestor-dashboard.js"></script>
@endsection


