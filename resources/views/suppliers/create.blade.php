@extends('layouts.app')
@section('title','Add Supplier')
@section('page_heading','Add Supplier')
@section('page_subheading','Enter supplier details')
@section('content')
    <!-- Supplier creation form (Authorized Personnel only) -->
    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        <div class="card"><div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input class="form-control" name="name" required maxlength="255" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">VAT Type</label>
                    <select class="form-select" name="vat_type">
                        <option value="">-- None --</option>
                        <option value="VAT">VAT</option>
                        <option value="Non-VAT">Non-VAT</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="2" maxlength="500"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input class="form-control" name="contact_person" maxlength="100" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Number</label>
                    <input class="form-control" name="contact_number" maxlength="20" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">TIN No.</label>
                    <input class="form-control" name="tin_no" maxlength="20" />
                </div>
            </div>
        </div></div>
        <div class="mt-3 d-flex justify-content-end">
            <button class="btn btn-primary" type="submit">Create</button>
        </div>
    </form>
@endsection



