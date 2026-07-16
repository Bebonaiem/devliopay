<div x-data="{ show: @json($show ?? false) }"
     x-show="show"
     x-cloak
     @keydown.escape.window="show = false"
     {{ $attributes }}
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="show" x-transition.opacity
             class="fixed inset-0 bg-black/50 backdrop-blur-sm"
             @click="show = false">
        </div>
        <div x-show="show" x-transition
             @click.away="show = false"
             class="relative bg-gray-900 border border-white/10 rounded-xl shadow-xl w-full max-w-lg p-6">
            @if(isset($title))
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
                    <button @click="show = false" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            <div>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
