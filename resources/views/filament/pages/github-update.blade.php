<x-filament::page>
    <div class="space-y-6">
        {{-- Current Status --}}
        <x-filament::section>
            <x-slot name="heading">Current Installation</x-slot>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Branch</span>
                    <p class="text-lg font-semibold">{{ $currentBranch }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Commit</span>
                    <p class="text-lg font-semibold font-mono">{{ $currentCommit }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Remote</span>
                    <p class="text-sm font-mono break-all">{{ $remoteUrl }}</p>
                </div>
            </div>
        </x-filament::section>

        {{-- Update Button --}}
        <x-filament::section>
            <x-slot name="heading">Update from GitHub</x-slot>
            <x-slot name="description">Pull the latest code, install dependencies, run migrations, and rebuild cache.</x-slot>

            <div class="flex items-center gap-4">
                <x-filament::button
                    wire:click="pullUpdates"
                    wire:loading.attr="disabled"
                    color="primary"
                    size="lg"
                    icon="heroicon-o-arrow-path"
                >
                    <span wire:loading.remove wire:target="pullUpdates">Pull & Update Now</span>
                    <span wire:loading wire:target="pullUpdates">Updating...</span>
                </x-filament::button>

                <x-filament::button
                    wire:click="loadGitInfo"
                    color="gray"
                    size="lg"
                    icon="heroicon-o-arrow-path"
                >
                    Refresh
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Update Output --}}
        @if($updateOutput)
            <x-filament::section>
                <x-slot name="heading">Update Output</x-slot>

                <div class="bg-gray-900 rounded-lg p-4 overflow-auto max-h-96">
                    <pre class="text-sm text-green-400 font-mono whitespace-pre-wrap">{{ $updateOutput }}</pre>
                </div>
            </x-filament::section>
        @endif

        {{-- Recent Commits --}}
        <x-filament::section>
            <x-slot name="heading">Recent Commits (Local)</x-slot>

            <div class="space-y-1">
                @foreach($commits as $commit)
                    <div class="text-sm font-mono text-gray-700 dark:text-gray-300 py-1 border-b border-gray-200 dark:border-gray-700">
                        {{ $commit }}
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament::page>
