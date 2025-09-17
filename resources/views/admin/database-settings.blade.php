@extends('layouts.app')
@section('title','Database Configuration')
@section('page_heading','Database Configuration')
@section('page_subheading','Configure database connection settings')
@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Database Connection Settings</h5>
            </div>
            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning">
                        {{ session('warning') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('superadmin.database.update') }}">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Changes to database settings will take effect after the application restarts.
                                Be careful when changing these settings as incorrect values may cause connection issues.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Database Host</label>
                                <input type="text" name="db_host" class="form-control" value="{{ $settings['db.host'] }}" required>
                                <div class="form-text">The hostname or IP address of your SQL Server</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Port</label>
                                <input type="number" name="db_port" class="form-control" value="{{ $settings['db.port'] }}" required>
                                <div class="form-text">Default port for SQL Server is 1433</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Database Name</label>
                                <input type="text" name="db_database" class="form-control" value="{{ $settings['db.database'] }}" required>
                                <div class="form-text">The name of your SQL Server database</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="db_username" class="form-control" value="{{ $settings['db.username'] }}" required>
                                <div class="form-text">SQL Server authentication username</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="db_password" class="form-control" placeholder="Leave blank to keep current password">
                                <div class="form-text">Leave blank to keep the current password</div>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Advanced Options</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Encrypt Connection</label>
                                <select name="db_encrypt" class="form-select">
                                    <option value="no" {{ $settings['db.encrypt'] == 'no' ? 'selected' : '' }}>No</option>
                                    <option value="yes" {{ $settings['db.encrypt'] == 'yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                                <div class="form-text">Enable encryption for the database connection</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Trust Server Certificate</label>
                                <select name="db_trust_server_certificate" class="form-select">
                                    <option value="true" {{ $settings['db.trust_server_certificate'] == 'true' ? 'selected' : '' }}>Yes</option>
                                    <option value="false" {{ $settings['db.trust_server_certificate'] == 'false' ? 'selected' : '' }}>No</option>
                                </select>
                                <div class="form-text">Trust the SQL Server certificate without validation</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Connection Pooling</label>
                                <select name="db_connection_pooling" class="form-select">
                                    <option value="true" {{ $settings['db.connection_pooling'] == 'true' ? 'selected' : '' }}>Enabled</option>
                                    <option value="false" {{ $settings['db.connection_pooling'] == 'false' ? 'selected' : '' }}>Disabled</option>
                                </select>
                                <div class="form-text">Enable connection pooling for better performance</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Multiple Active Result Sets</label>
                                <select name="db_multiple_active_result_sets" class="form-select">
                                    <option value="true" {{ $settings['db.multiple_active_result_sets'] == 'true' ? 'selected' : '' }}>Enabled</option>
                                    <option value="false" {{ $settings['db.multiple_active_result_sets'] == 'false' ? 'selected' : '' }}>Disabled</option>
                                </select>
                                <div class="form-text">Allow multiple active result sets from a single connection</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Query Timeout (seconds)</label>
                                <input type="number" name="db_query_timeout" class="form-control" value="{{ $settings['db.query_timeout'] }}" required>
                                <div class="form-text">Maximum time to wait for a query to complete (in seconds)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Connection Timeout (seconds)</label>
                                <input type="number" name="db_timeout" class="form-control" value="{{ $settings['db.timeout'] }}" required>
                                <div class="form-text">Maximum time to wait for a connection (in seconds)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('superadmin.index') }}" class="btn btn-secondary">Back to Dashboard</a>
                        <button type="submit" class="btn btn-primary">Save Database Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection