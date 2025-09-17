@extends('layouts.app')
@section('title','Purchase Order #'.$po->purchase_order_no)
@section('page_heading','Purchase Order #'.$po->purchase_order_no)
@section('page_subheading',$po->purpose)
@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <div class="d-flex justify-content-end mb-2">
        <a class="btn btn-outline-warning me-2" href="{{ route('po.edit', $po->purchase_order_no) }}"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
  <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
</svg></a>
        <a class="btn btn-outline-secondary" href="{{ route('po.print', $po->purchase_order_no) }}" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
  <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
  <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/>
</svg></a>
    </div>
    <!-- PO detail: supplier, dates, items table, and totals summary -->
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card"><div class="card-body">
                <div class="row">
                    <div class="col-md-6"><div class="text-muted">Supplier</div><div class="fw-semibold">{{ $po->supplier_name }}</div></div>
                    <div class="col-md-3"><div class="text-muted">Date Requested</div><div class="fw-semibold">{{ $po->date_requested }}</div></div>
                    <div class="col-md-3"><div class="text-muted">Delivery Date</div><div class="fw-semibold">{{ $po->delivery_date }}</div></div>
                </div>
            </div></div>
            <div class="card mt-3"><div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @foreach($items as $it)
                            <tr><td>{{ $it->item_description }}</td><td class="text-end">{{ $it->quantity }}</td><td class="text-end">{{ number_format($it->unit_price,2) }}</td><td class="text-end">{{ number_format($it->total_cost,2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card"><div class="card-body">
                <div class="d-flex justify-content-between"><span class="text-muted">Shipping</span><span>{{ number_format($po->shipping_fee,2) }}</span></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Discount</span><span>{{ number_format($po->discount,2) }}</span></div>
                <div class="d-flex justify-content-between"><span class="text-muted">VaTable Sales (Ex Vat)</span><span></span></div>
                <div class="d-flex justify-content-between"><span class="text-muted">12% Vat</span><span></span></div>
                <hr>
                <div class="d-flex justify-content-between fw-semibold"><span>Total</span><span>{{ number_format($po->total,2) }}</span></div>
            </div></div>
            <div class="card mt-3"><div class="card-body">
                <div class="text-muted">Status</div>
                <div class="h5">{{ $po->status_name ?? 'Draft' }}</div>
                <div class="text-muted">Remarks</div>
                <div>{{ $po->remarks ?? '—' }}</div>
            </div></div>
        </div>
    </div>
@endsection