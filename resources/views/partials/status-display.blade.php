{{-- 
    Shared status display component
    Usage: @include('partials.status-display', ['status' => $statusName, 'type' => 'circle|badge'])
--}}

@php
    $statusName = $status ?? 'Unknown';
    $statusClass = strtolower(str_replace(' ', '', $statusName));
    $displayType = $type ?? 'circle'; // 'circle' or 'badge'
    
    // Status color mapping
    $statusColors = [
        'pending' => ['bg' => '#ffc107', 'text' => '#000', 'bootstrap' => 'warning'],
        'verified' => ['bg' => '#17a2b8', 'text' => '#fff', 'bootstrap' => 'info'],
        'approved' => ['bg' => '#28a745', 'text' => '#fff', 'bootstrap' => 'success'],
        'received' => ['bg' => '#6f42c1', 'text' => '#fff', 'bootstrap' => 'primary'],
        'rejected' => ['bg' => '#dc3545', 'text' => '#fff', 'bootstrap' => 'danger'],
        'cancelled' => ['bg' => '#6c757d', 'text' => '#fff', 'bootstrap' => 'secondary']
    ];
    
    $colors = $statusColors[$statusClass] ?? ['bg' => '#6c757d', 'text' => '#fff', 'bootstrap' => 'secondary'];
@endphp

@if($displayType === 'circle')
    <div class="status-display d-flex align-items-center" style="gap: 8px;">
        <span class="status-circle" style="width: 10px; height: 10px; border-radius: 50%; background: {{ $colors['bg'] }}; flex-shrink: 0;"></span>
        <span class="status-text" style="font-size: 14px; font-weight: 500;">{{ $statusName }}</span>
    </div>
@else
    <span class="badge bg-{{ $colors['bootstrap'] }}">{{ $statusName }}</span>
@endif
