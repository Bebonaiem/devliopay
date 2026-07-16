@php
    $colors = [
        'success' => 'bg-green-900/30 border border-green-800/50 text-green-300',
        'error' => 'bg-red-900/30 border border-red-800/50 text-red-300',
        'warning' => 'bg-yellow-900/30 border border-yellow-800/50 text-yellow-300',
        'info' => 'bg-blue-900/30 border border-blue-800/50 text-blue-300',
    ];
    $icons = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle',
    ];
    $type = $type ?? 'info';
@endphp
<div {{ $attributes->merge(['class' => $colors[$type] . ' px-4 py-3 rounded-lg flex items-center gap-2']) }}>
    <i class="fas {{ $icons[$type] }}"></i>
    <span>{{ $slot }}</span>
</div>
