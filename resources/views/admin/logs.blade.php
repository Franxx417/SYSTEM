@extends('layouts.app')
@section('title','System Logs')
@section('page_heading','System Logs')
@section('page_subheading','View and manage application logs')
@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Application Logs</span>
            <div>
                <a href="{{ route('superadmin.index', ['tab' => 'logs']) }}" class="btn btn-sm btn-outline-secondary">Back to SuperAdmin</a>
                <form method="POST" action="{{ route('superadmin.logs.clear') }}" class="d-inline" onsubmit="return confirm('Clear all logs? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">Clear All Logs</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if($errors->has('logs'))
                <div class="alert alert-danger">{{ $errors->first('logs') }}</div>
            @endif
            
            @if(empty($logs) || (count($logs) === 1 && empty($logs[0])))
                <div class="text-center text-muted py-4">
                    <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                    No logs found or log file is empty.
                </div>
            @else
                <div class="log-container" style="max-height: 600px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px;">
                    @foreach($logs as $line)
                        @if(!empty(trim($line)))
                            <div class="log-line mb-1" style="border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">
                                @if(str_contains($line, 'ERROR'))
                                    <span class="text-danger">{{ $line }}</span>
                                @elseif(str_contains($line, 'WARNING'))
                                    <span class="text-warning">{{ $line }}</span>
                                @elseif(str_contains($line, 'INFO'))
                                    <span class="text-info">{{ $line }}</span>
                                @else
                                    <span class="text-muted">{{ $line }}</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="mt-3 text-muted small">
                    Showing last 100 log entries. Logs are displayed in reverse chronological order (newest first).
                </div>
            @endif
        </div>
    </div>
@endsection
