@php
    $colors = [
        'success' => 'bg-green-500/10 text-green-400 border-green-500/20',
        'danger' => 'bg-red-500/10 text-red-400 border-red-500/20',
        'warning' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
        'info' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'gray' => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
        'primary' => 'bg-primary-500/10 text-primary-400 border-primary-500/20',
    ];
    $color = $color ?? 'gray';
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . ($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>
