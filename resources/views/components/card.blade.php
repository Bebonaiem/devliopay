<div {{ $attributes->merge(['class' => 'bg-gray-900 border border-white/5 rounded-xl overflow-hidden']) }}>
    @if(isset($header))
        <div class="px-6 py-4 border-b border-white/5 bg-gray-800/30">
            {{ $header }}
        </div>
    @endif
    <div class="px-6 py-5 {{ $attributes->get('bodyClass', '') }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="px-6 py-3 border-t border-white/5 bg-gray-800/20">
            {{ $footer }}
        </div>
    @endif
</div>
