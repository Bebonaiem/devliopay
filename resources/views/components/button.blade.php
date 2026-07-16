@php
    $variants = [
        'primary' => 'bg-primary-600 hover:bg-primary-500 text-white',
        'secondary' => 'border border-white/10 hover:bg-white/5 text-gray-300',
        'danger' => 'bg-red-600 hover:bg-red-500 text-white',
        'success' => 'bg-green-600 hover:bg-green-500 text-white',
        'ghost' => 'text-gray-400 hover:text-white hover:bg-white/5',
    ];
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];
    $variant = $variant ?? 'primary';
    $size = $size ?? 'md';
@endphp
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 font-medium rounded-lg transition duration-75 ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    {{ $slot }}
</button>
