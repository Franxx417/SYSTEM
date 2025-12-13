@extends('layouts.app')
@section('title','Requestor Dashboard')
@section('page_heading','Procurement Dashboard')
@section('page_subheading','Quick overview of your operations')

@section('content')
<link rel="stylesheet" href="{{ route('dynamic.status.css') }}">

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted">My POs</div>
            <div class="h3 mb-0">{{ $metrics['my_total'] ?? 0 }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted">Verified</div>
            <div class="h3 mb-0">{{ $metrics['my_verified'] ?? 0 }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted">Approved</div>
            <div class="h3 mb-0">{{ $metrics['my_approved'] ?? 0 }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="fw-semibold">Purchase Orders</div>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('po.index') }}">Open Full List</a>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end">
                    <input type="hidden" name="tab" value="overview" />
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date From</label>
                        <input type="date" class="form-control" name="po_date_from" value="{{ request('po_date_from') }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Date To</label>
                        <input type="date" class="form-control" name="po_date_to" value="{{ request('po_date_to') }}" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Status</label>
                        <select class="form-select" name="po_status">
                            <option value="">All</option>
                            @foreach(($statusOptions ?? []) as $st)
                                <option value="{{ $st->status_id }}" @selected(request('po_status') == $st->status_id)>{{ $st->status_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Amount Range</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" class="form-control" name="po_min_total" placeholder="Min" value="{{ request('po_min_total') }}" />
                            <input type="number" step="0.01" min="0" class="form-control" name="po_max_total" placeholder="Max" value="{{ request('po_max_total') }}" />
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small mb-1">Sort By</label>
                        <select class="form-select" name="po_sort_by">
                            <option value="created_at" @selected(request('po_sort_by','created_at')==='created_at')>Created</option>
                            <option value="date_requested" @selected(request('po_sort_by')==='date_requested')>Date Requested</option>
                            <option value="total" @selected(request('po_sort_by')==='total')>Total</option>
                            <option value="purchase_order_no" @selected(request('po_sort_by')==='purchase_order_no')>PO No.</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1">Direction</label>
                        <select class="form-select" name="po_sort_dir">
                            <option value="desc" @selected(request('po_sort_dir','desc')==='desc')>Desc</option>
                            <option value="asc" @selected(request('po_sort_dir')==='asc')>Asc</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Apply</button>
                        <a class="btn btn-outline-secondary" href="{{ route('dashboard') }}">Reset</a>
                    </div>
                </form>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>PO No.</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Vendor</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($purchaseOrders ?? []) as $po)
                                <tr>
                                    <td>{{ $po->purchase_order_no }}</td>
                                    <td>{{ $po->date_requested }}</td>
                                    <td>
                                        @include('partials.status-display', ['status' => $po->status_name ?? 'Pending', 'type' => 'text'])
                                    </td>
                                    <td>{{ $po->supplier_name ?? '—' }}</td>
                                    <td class="text-end">₱{{ number_format($po->total ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No purchase orders found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($purchaseOrders) && method_exists($purchaseOrders, 'links'))
                    <div class="d-flex justify-content-end">{{ $purchaseOrders->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
