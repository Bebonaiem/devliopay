@php
    $baseClasses = 'w-full bg-gray-800 border border-white/10 text-white placeholder-gray-500 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 px-4 py-2';
@endphp

@if($type ?? 'text' === 'select')
    <select {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </select>
@elseif($type === 'textarea')
    <textarea {{ $attributes->merge(['class' => $baseClasses . ' resize-vertical', 'rows' => 3]) }}>{{ $slot }}</textarea>
@else
    <input {{ $attributes->merge(['type' => $type ?? 'text', 'class' => $baseClasses]) }} />
@endif
