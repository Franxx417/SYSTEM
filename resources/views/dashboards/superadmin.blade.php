@extends('layouts.app')
@section('title','Superadmin Control Panel')
@section('page_heading','Superadmin')
@section('page_subheading','100% control of the system')
@section('content')
    <!-- Summary cards: total POs, pending POs, suppliers, users -->
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Total POs</div>
                    <div class="h3 mb-0">{{ $metrics['total_pos'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Pending POs</div>
                    <div class="h3 mb-0">{{ $metrics['pending_pos'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Suppliers</div>
                    <div class="h3 mb-0">{{ $metrics['suppliers'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Users</div>
                    <div class="h3 mb-0">{{ $metrics['users'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Recent Purchase Orders</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Quick actions">
                        <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">Users</a>
                        <a class="btn btn-outline-primary" href="{{ route('admin.users.create') }}">Add User</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>PO No.</th>
                                <th>Purpose</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPOs ?? [] as $po)
                                <tr>
                                    <td>{{ $po->purchase_order_no }}</td>
                                    <td>{{ $po->purpose }}</td>
                                    <td class="text-end">₱{{ number_format($po->total, 2) }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewPO('{{ $po->purchase_order_no }}')">View</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No purchase orders found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Suppliers</div>
                <ul class="list-group list-group-flush">
                    @forelse($suppliers ?? [] as $supplier)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $supplier->name }}</span>
                            <span class="text-muted">{{ $supplier->vat_type }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No suppliers found</li>
                    @endforelse
                </ul>
            </div>
            <a class="btn btn-outline-primary w-100 mt-3" href="{{ route('admin.users.index') }}">Manage Users</a>
        </div>
    </div>

    @php($active = request()->get('tab','branding'))
    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><a class="nav-link @if($active==='branding') active @endif" href="{{ route('superadmin.index',['tab'=>'branding']) }}">Branding</a></li>
        <li class="nav-item"><a class="nav-link @if($active==='users') active @endif" href="{{ route('superadmin.index',['tab'=>'users']) }}">Users</a></li>
        <li class="nav-item"><a class="nav-link @if($active==='database') active @endif" href="{{ route('superadmin.index',['tab'=>'database']) }}">Database</a></li>
        <li class="nav-item"><a class="nav-link @if($active==='system') active @endif" href="{{ route('superadmin.index',['tab'=>'system']) }}">System</a></li>
        <li class="nav-item"><a class="nav-link @if($active==='logs') active @endif" href="{{ route('superadmin.index',['tab'=>'logs']) }}">Logs</a></li>
        <li class="nav-item"><a class="nav-link @if($active==='query') active @endif" href="{{ route('superadmin.index',['tab'=>'query']) }}">Query</a></li>
    </ul>

    @if($active==='branding')
        <div class="card border-0 shadow-sm">
            <div class="card-header">Branding</div>
            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning">{{ session('warning') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-1">Please fix the following:</div>
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('superadmin.branding') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Application Name</label>
                        <input class="form-control" type="text" name="app_name" value="{{ $settings['app.name'] ?? '' }}" maxlength="100" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo (PNG/JPG/SVG)</label>
                        <input class="form-control" type="file" name="logo" accept=".png,.jpg,.jpeg,.svg" />
                        @if(!empty($settings['branding.logo_path']))
                            <div class="mt-2">
                                <img src="{{ $settings['branding.logo_path'] }}" alt="Logo" style="height:48px;width:auto"/>
                            </div>
                        @endif
                    </div>
                    <button class="btn btn-primary">Save Branding</button>
                </form>
            </div>
        </div>
    @elseif($active==='users')
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>User Management</span>
                <div>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.index') }}">Manage Users</a>
                    <a class="btn btn-sm btn-primary" href="{{ route('admin.users.create') }}">Add User</a>
                </div>
            </div>
            <div class="card-body">
                <div class="text-muted">Use the buttons above to manage users and roles.</div>
            </div>
        </div>
    @elseif($active==='database')
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">Database Configuration</div>
                    <div class="card-body">
                        <a href="{{ route('superadmin.database') }}" class="btn btn-primary mb-2">Database Settings</a>
                        <div class="text-muted small">Adjust connection, encryption, and timeouts.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">Table Information</div>
                    <div class="card-body">
                        <button class="btn btn-outline-primary" onclick="loadTableInfo()">Load Table Info</button>
                        <div id="table-info" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($active==='system')
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">Maintenance</div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.system') }}" class="mb-2 d-inline">
                    @csrf
                    <input type="hidden" name="action" value="cache_clear" />
                    <button type="submit" class="btn btn-warning">Clear Cache</button>
                </form>
                <form method="POST" action="{{ route('superadmin.system') }}" class="mb-2 d-inline">
                    @csrf
                    <input type="hidden" name="action" value="backup_full" />
                    <button type="submit" class="btn btn-primary">Backup Full System</button>
                </form>
                @if($errors->has('backup'))
                    <div class="text-danger small mt-2">{{ $errors->first('backup') }}</div>
                @endif
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">Statuses</div>
            <div class="card-body">
                <div class="row g-2 align-items-end mb-3">
                    <form method="POST" action="{{ route('superadmin.system') }}" class="col-md-6">
                        @csrf
                        <input type="hidden" name="action" value="status_create" />
                        <label class="form-label">Add New Status</label>
                        <div class="input-group">
                            <input class="form-control" type="text" name="status_name" maxlength="100" placeholder="e.g., On Hold" required />
                            <button class="btn btn-outline-primary" type="submit">Add</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr><th>Status</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            @foreach(($statuses ?? []) as $st)
                                <tr>
                                    <td>
                                        <form method="POST" action="{{ route('superadmin.system') }}" class="d-flex gap-2">
                                            @csrf
                                            <input type="hidden" name="action" value="status_update" />
                                            <input type="hidden" name="status_id" value="{{ $st->status_id }}" />
                                            <input class="form-control" type="text" name="status_name" value="{{ $st->status_name }}" maxlength="100" />
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">Save</button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('superadmin.system') }}" onsubmit="return confirm('Delete this status?');" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="action" value="status_delete" />
                                            <input type="hidden" name="status_id" value="{{ $st->status_id }}" />
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">Export Data</div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.system') }}">
                    @csrf
                    <input type="hidden" name="action" value="export_data" />
                    <div class="row">
                        @php($tables = ['settings','suppliers','users','login','role_types','roles','statuses','purchase_orders','items','approvals'])
                        @foreach($tables as $t)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tables[]" id="t-{{ $t }}" value="{{ $t }}">
                                    <label class="form-check-label" for="t-{{ $t }}">{{ $t }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="submit" class="btn btn-outline-primary mt-2">Download JSON</button>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header">Danger Zone</div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.system') }}" enctype="multipart/form-data" class="mb-3">
                    @csrf
                    <input type="hidden" name="action" value="restore_backup" />
                    <label class="form-label">Restore from Backup (JSON or ZIP)</label>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <input class="form-control" type="file" name="backup_file" accept=".json,.zip" required />
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="wipe_all" name="wipe_all">
                                <label class="form-check-label" for="wipe_all">Wipe all before restore</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-danger mt-2">Restore</button>
                    @if($errors->has('restore'))
                        <div class="text-danger small mt-2">{{ $errors->first('restore') }}</div>
                    @endif
                </form>
                <form method="POST" action="{{ route('superadmin.system') }}" onsubmit="return confirm('This will clear selected table data. Continue?');">
                    @csrf
                    <input type="hidden" name="action" value="truncate_table" />
                    <div class="row g-2 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Table to Clear</label>
                            <select class="form-select" name="table" required>
                                @foreach(['items','approvals','roles','login','purchase_orders'] as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-danger">Clear Table Data</button>
                        </div>
                    </div>
                </form>
                <div class="text-muted small mt-2">Note: Clear child tables first to avoid foreign key conflicts.</div>
            </div>
        </div>
    @elseif($active==='logs')
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>System Logs</span>
                <div>
                    <a href="{{ route('superadmin.logs') }}" class="btn btn-sm btn-outline-primary">View Full Logs</a>
                    <form method="POST" action="{{ route('superadmin.logs.clear') }}" class="d-inline" onsubmit="return confirm('Clear all logs?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">Clear Logs</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="text-muted">Use the buttons above to view or clear system logs.</div>
            </div>
        </div>
    @elseif($active==='query')
        <div class="card border-0 shadow-sm">
            <div class="card-header">Database Query Tool</div>
            <div class="card-body">
                @if(session('query_results'))
                    <div class="alert alert-success">
                        <strong>Query executed successfully:</strong> {{ session('executed_query') }}
                        <div class="mt-2">
                            <strong>Results ({{ count(session('query_results')) }} rows):</strong>
                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered">
                                    @if(count(session('query_results')) > 0)
                                        <thead>
                                            <tr>
                                                @foreach(array_keys((array)session('query_results')[0]) as $column)
                                                    <th>{{ $column }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(session('query_results') as $row)
                                                <tr>
                                                    @foreach((array)$row as $value)
                                                        <td>{{ $value }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
                @if($errors->has('query'))
                    <div class="alert alert-danger">{{ $errors->first('query') }}</div>
                @endif
                <form method="POST" action="{{ route('superadmin.query') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">SQL Query (SELECT only)</label>
                        <textarea class="form-control" name="query" rows="5" placeholder="SELECT * FROM users LIMIT 10" required>{{ old('query') }}</textarea>
                        <div class="form-text">Only SELECT queries are allowed for security reasons.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Execute Query</button>
                </form>
            </div>
        </div>
    @endif

    <script>
        function viewPO(poNo) {
            window.open('/po/' + poNo, '_blank');
        }
        
        function loadTableInfo() {
            fetch('{{ route("superadmin.database.info") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('table-info').innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                        return;
                    }
                    
                    let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Table</th><th>Rows</th><th>Columns</th></tr></thead><tbody>';
                    
                    Object.values(data).forEach(table => {
                        html += `<tr><td>${table.name}</td><td>${table.count}</td><td>${table.columns.length}</td></tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    document.getElementById('table-info').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('table-info').innerHTML = '<div class="alert alert-danger">Failed to load table info</div>';
                });
        }
    </script>
@endsection


