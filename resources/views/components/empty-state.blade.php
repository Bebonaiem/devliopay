<div {{ $attributes->merge(['class' => 'text-center py-12 bg-gray-900 border border-white/5 rounded-xl']) }}>
    @if(isset($icon))
        <i class="{{ $icon }} text-gray-600 text-5xl mb-4"></i>
    @else
        <i class="fas fa-inbox text-gray-600 text-5xl mb-4"></i>
    @endif
    <p class="text-gray-500 text-lg mb-4">{{ $slot }}</p>
    @if(isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
