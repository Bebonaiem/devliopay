<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <div class="text-lg font-bold">{{ $getBrandName() }}</div>
                <div class="text-xs text-gray-500">{{ $getVersion() }}</div>
            </div>
            <div class="flex flex-col items-end gap-1">
                <a href="{{ Setting::get('company_url', config('app.url', '#')) }}" target="_blank" class="text-xs text-gray-400 hover:text-white transition-colors flex items-center gap-1">
                    <x-heroicon-o-globe-alt class="w-3 h-3" /> Website
                </a>
                <a href="https://github.com/Bebonaiem/devliopay" target="_blank" class="text-xs text-gray-400 hover:text-white transition-colors flex items-center gap-1">
                    <x-heroicon-o-code-bracket class="w-3 h-3" /> GitHub
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
