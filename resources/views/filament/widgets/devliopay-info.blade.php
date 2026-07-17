<div class="filament-widgets-widget">
    <div class="fi-section rounded-xl bg-white dark:bg-white/5 shadow-sm">
        <div class="fi-section-content p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-lg font-bold">{{ $this->getBrandName() }}</div>
                    <div class="text-xs text-gray-500">{{ $this->getVersion() }}</div>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <a href="{{ \App\Models\Setting::get('company_url', config('app.url', '#')) }}" target="_blank" class="text-xs text-gray-400 hover:text-white transition-colors">
                        Website
                    </a>
                    <a href="https://github.com/Bebonaiem/devliopay" target="_blank" class="text-xs text-gray-400 hover:text-white transition-colors">
                        GitHub
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
