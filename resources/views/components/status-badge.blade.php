@php
    $statusColors = [
        'pending' => 'warning',
        'active' => 'success',
        'paid' => 'success',
        'suspended' => 'danger',
        'cancelled' => 'gray',
        'terminated' => 'danger',
        'overdue' => 'danger',
        'refunded' => 'info',
        'completed' => 'success',
        'open' => 'info',
        'closed' => 'gray',
        'on_hold' => 'warning',
    ];
    $color = $statusColors[$status] ?? 'gray';
@endphp
<x-badge :color="$color">{{ ucfirst($status) }}</x-badge>
