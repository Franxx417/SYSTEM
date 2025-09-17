@extends('layouts.app')
@section('title','Edit Supplier')
@section('page_heading','Edit Supplier')
@section('page_subheading','Update supplier details')
@section('content')
    <form method="POST" action="{{ route('suppliers.update', $supplier->supplier_id) }}">
        @csrf
        <div class="card"><div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input class="form-control" name="name" value="{{ old('name', $supplier->name) }}" required maxlength="255" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">VAT Type</label>
                    <select class="form-select" name="vat_type">
                        <option value="" {{ old('vat_type', $supplier->vat_type) === null ? 'selected' : '' }}>-- None --</option>
                        <option value="VAT" {{ old('vat_type', $supplier->vat_type) === 'VAT' ? 'selected' : '' }}>VAT</option>
                        <option value="Non-VAT" {{ old('vat_type', $supplier->vat_type) === 'Non-VAT' || old('vat_type', $supplier->vat_type) === 'Non_VAT' ? 'selected' : '' }}>Non-VAT</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="address" rows="2" maxlength="500">{{ old('address', $supplier->address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Person</label>
                    <input class="form-control" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" maxlength="100" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Number</label>
                    <input class="form-control" name="contact_number" value="{{ old('contact_number', $supplier->contact_number) }}" maxlength="20" />
                </div>
                <div class="col-md-6">
                    <label class="form-label">TIN No.</label>
                    <input class="form-control" name="tin_no" value="{{ old('tin_no', $supplier->tin_no) }}" maxlength="20" />
                </div>
            </div>
        </div></div>
        <div class="mt-3 d-flex justify-content-end">
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button class="btn btn-primary" type="submit">Save Changes</button>
        </div>
    </form>
@endsection
