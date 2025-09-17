@extends('layouts.app')
@section('title','Suppliers')
@section('page_heading','Suppliers')
@section('page_subheading','Manage suppliers')
@section('content')
    <!-- Suppliers list; Authorized Personnel can add new ones -->
    <div class="d-flex justify-content-end mb-2"><a class="btn btn-primary" href="{{ route('suppliers.create') }}">Add Supplier</a></div>
    <div class="card"><div class="card-body p-0">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>VAT Type</th><th>Contact</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @foreach($suppliers as $s)
                    <tr>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->vat_type ?? '—' }}</td>
                        <td>{{ $s->contact_person }} {{ $s->contact_number ? ' - '.$s->contact_number : '' }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="editSupplier('{{ $s->supplier_id }}', '{{ addslashes($s->name) }}', '{{ $s->vat_type }}', '{{ addslashes($s->address ?? '') }}', '{{ addslashes($s->contact_person ?? '') }}', '{{ $s->contact_number ?? '' }}', '{{ $s->tin_no ?? '' }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                        <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                    </svg>
                                </button>
                                <button class="btn btn-outline-danger" onclick="deleteSupplier('{{ $s->supplier_id }}', '{{ addslashes($s->name) }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
    <div class="mt-2">{{ $suppliers->links() }}</div>

    <!-- Edit Supplier Modal -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editSupplierForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input class="form-control" name="name" id="edit_name" required maxlength="255" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">VAT Type</label>
                                <select class="form-select" name="vat_type" id="edit_vat_type">
                                    <option value="">-- None --</option>
                                    <option value="VAT">VAT</option>
                                    <option value="Non-VAT">Non-VAT</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" id="edit_address" rows="2" maxlength="500"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input class="form-control" name="contact_person" id="edit_contact_person" maxlength="100" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input class="form-control" name="contact_number" id="edit_contact_number" maxlength="20" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">TIN No.</label>
                                <input class="form-control" name="tin_no" id="edit_tin_no" maxlength="20" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Supplier Modal -->
    <div class="modal fade" id="deleteSupplierModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-danger me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                                <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                            </svg>
                        </div>
                        <div>
                            <h6 class="mb-1">Are you sure you want to delete this supplier?</h6>
                            <p class="mb-0 text-muted">This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <strong>Supplier:</strong> <span id="delete_supplier_name"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteSupplierForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Supplier</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/suppliers-index.js"></script>
@endsection



