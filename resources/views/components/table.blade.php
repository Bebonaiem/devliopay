<div class="bg-gray-900 border border-white/5 rounded-xl overflow-hidden">
    <table {{ $attributes->merge(['class' => 'w-full']) }}>
        @if(isset($header))
            <thead class="bg-gray-800/50">
                <tr>
                    {{ $header }}
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-white/5">
            {{ $slot }}
        </tbody>
    </table>
</div>
