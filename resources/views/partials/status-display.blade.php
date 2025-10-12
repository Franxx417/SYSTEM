{{-- 
    Shared status display component (No color indicators)
    Usage: @include('partials.status-display', ['status' => $statusName, 'type' => 'text|badge'])
--}}

@php
    $statusName = $status ?? 'Unknown';
    $displayType = $type ?? 'text';
@endphp

@if($displayType === 'badge')
    <span class="badge bg-secondary">{{ $statusName }}</span>
@else
    <span class="status-text" style="font-size: 14px; font-weight: 500;">{{ $statusName }}</span>
@endif
