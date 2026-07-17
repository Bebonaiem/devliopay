@extends('layouts.client')

@section('title', 'Service Details')

@section('content')
@php $symbol = $service->pricing->currencies->first()?->symbol ?? $defaultCurrencySymbol; @endphp
<div class="space-y-6">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('client.services.index') }}" class="hover:text-gray-300 transition-colors">Services</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-300">{{ $service->name ?? $service->product->name ?? 'Service' }}</span>
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center">
                <i data-lucide="server" class="w-6 h-6 text-brand-400"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ $service->name ?? $service->product->name ?? 'Service' }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider w-fit
                {{ ($service->status ?? '') === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                {{ ($service->status ?? '') === 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
                {{ ($service->status ?? '') === 'suspended' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : '' }}
                {{ !in_array($service->status ?? '', ['active','pending','suspended']) ? 'bg-gray-500/10 text-gray-400 border border-gray-500/20' : '' }}
            ">{{ $service->status ?? 'unknown' }}</span>
            @if($service->status === 'active' && $service->server_extension === 'pterodactyl' && ($service->server_properties['identifier'] ?? $service->server_properties['server_id'] ?? null))
            <a href="{{ $panelUrl }}" target="_blank" class="px-4 py-1.5 rounded-xl text-xs font-semibold text-brand-400 bg-brand-500/10 hover:bg-brand-500/20 border border-brand-500/20 transition-all inline-flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Open in Panel
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Details --}}
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-4">Service Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Product</p>
                        <p class="text-sm font-medium">{{ $service->product->name ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Plan</p>
                        <p class="text-sm font-medium">{{ $service->pricing->name ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Next Due Date</p>
                        <p class="text-sm font-medium">{{ $service->next_billing_at ? $service->next_billing_at->format('M j, Y') : '-' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Amount</p>
                        <p class="text-sm font-medium">{{ $symbol }}{{ number_format($service->pricing->currencies->first()?->pivot->amount ?? 0, 2) }}</p>
                    </div>
                    @if($service->server_extension === 'pterodactyl' && ($service->server_properties['ip_address'] ?? null))
                    <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Server IP</p>
                        <p class="text-sm font-mono font-medium">{{ $service->server_properties['ip_address'] }}:{{ $service->server_properties['port'] ?? '' }}</p>
                    </div>
                    @endif
                    <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5">
                        <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Registration Date</p>
                        <p class="text-sm font-medium">{{ $service->created_at ? $service->created_at->format('M j, Y') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Server Info --}}
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-4">Server Information</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">IP Address</span>
                        <span class="text-sm font-mono text-gray-300">{{ $service->server_properties['ip_address'] ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Port</span>
                        <span class="text-sm font-mono text-gray-300">{{ $service->server_properties['port'] ?? '-' }}</span>
                    </div>
                    @php
                        $cpu = $service->config_options['cpu'] ?? $service->product->config_options['cpu'] ?? '-';
                        $ram = $service->config_options['ram'] ?? $service->product->config_options['ram'] ?? '-';
                        $disk = $service->config_options['disk'] ?? $service->product->config_options['disk'] ?? '-';
                    @endphp
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">CPU</span>
                        <span class="text-sm text-gray-300">{{ $cpu }}%</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Memory</span>
                        <span class="text-sm text-gray-300">{{ $ram }} MB</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Disk</span>
                        <span class="text-sm text-gray-300">{{ $disk }} MB</span>
                    </div>
                    @if($service->server_extension === 'pterodactyl')
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Server ID</span>
                        <span class="text-sm font-mono text-gray-300">{{ $service->server_properties['server_id'] ?? '-' }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Available Addons --}}
            @if(isset($availableAddons) && count($availableAddons) > 0)
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-4">Available Addons</h2>
                <div class="space-y-3">
                    @foreach($availableAddons as $addon)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white/[0.02] border border-white/5">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium">{{ $addon->name }}</p>
                            <p class="text-xs text-gray-500">{{ $addon->description ?? $addon->billing_interval }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold">{{ $defaultCurrencySymbol }}{{ number_format($addon->price, 2) }}<span class="text-gray-500 font-normal text-xs">/{{ $addon->billing_interval }}</span></span>
                            <form method="POST" action="{{ route('client.services.purchase-addon', $service->id) }}">
                                @csrf
                                <input type="hidden" name="addon_id" value="{{ $addon->id }}">
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-brand-400 bg-brand-500/10 hover:bg-brand-500/20 border border-brand-500/20 transition-all">
                                    Add
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Active Addons --}}
            @if($service->addons && count($service->addons) > 0)
            <div class="glass rounded-2xl p-6">
                <h2 class="text-sm font-semibold mb-4">Active Addons</h2>
                <div class="space-y-3">
                    @foreach($service->addons as $addon)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white/[0.02] border border-white/5">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium">{{ $addon->name }}</p>
                            <p class="text-xs text-gray-500">{{ $addon->pivot->status ?? 'active' }}</p>
                        </div>
                        <span class="text-sm font-semibold">{{ $defaultCurrencySymbol }}{{ number_format($addon->pivot->price ?? $addon->price, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Danger Zone --}}
            @if(!in_array($service->status, ['cancelled', 'terminated']))
            <div class="glass rounded-2xl p-6 border border-red-500/10" x-data="{ cancelStep: 'none' }">
                <h2 class="text-sm font-semibold text-red-400 mb-4">Danger Zone</h2>
                <div class="space-y-2">
                    {{-- Initial cancel button --}}
                    <div x-show="cancelStep === 'none'">
                        <button @click="cancelStep = 'choose'" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-red-400 bg-red-500/5 hover:bg-red-500/10 border border-red-500/10 hover:border-red-500/20 transition-all text-left">
                            <i data-lucide="x-circle" class="w-3.5 h-3.5 inline mr-2"></i> Cancel Service
                        </button>
                    </div>

                    {{-- Choose cancel type --}}
                    <div x-show="cancelStep === 'choose'" x-cloak class="space-y-2">
                        <p class="text-xs text-gray-400 mb-2">How would you like to cancel?</p>
                        <button @click="cancelStep = 'confirm-end'" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-amber-400 bg-amber-500/5 hover:bg-amber-500/10 border border-amber-500/10 hover:border-amber-500/20 transition-all text-left">
                            <i data-lucide="clock" class="w-3.5 h-3.5 inline mr-2"></i> Cancel at End of Billing Period
                        </button>
                        <button @click="cancelStep = 'confirm-now'" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-red-400 bg-red-500/5 hover:bg-red-500/10 border border-red-500/10 hover:border-red-500/20 transition-all text-left">
                            <i data-lucide="zap" class="w-3.5 h-3.5 inline mr-2"></i> Cancel Immediately
                        </button>
                        <button @click="cancelStep = 'none'" class="w-full px-4 py-2 rounded-xl text-xs font-medium text-gray-500 hover:text-gray-300 transition-all">
                            Go Back
                        </button>
                    </div>

                    {{-- Confirm cancel at end of billing --}}
                    <div x-show="cancelStep === 'confirm-end'" x-cloak class="space-y-2">
                        <div class="p-3 rounded-xl bg-amber-500/5 border border-amber-500/10">
                            <p class="text-xs text-amber-300 font-medium">Cancel at end of billing period</p>
                            <p class="text-[11px] text-gray-500 mt-1">Your service will remain active until {{ $service->next_billing_at ? $service->next_billing_at->format('M j, Y') : 'the end of the billing period' }}, then it will be terminated. No further invoices will be generated.</p>
                        </div>
                        <div class="flex gap-2">
                            <form id="cancel-end-form" method="POST" action="{{ route('client.services.cancel', $service->id) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="cancel_type" value="end_of_period">
                                <button type="button" onclick="showConfirm({title: 'Cancel Service', message: 'Your service will be cancelled at the end of the billing period. No further invoices will be generated. Are you sure?', type: 'warning', confirmText: 'Yes, Cancel', callback: 'cancel-end-form'})" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-amber-500 hover:bg-amber-400 transition-all">
                                    Confirm
                                </button>
                            </form>
                            <button @click="cancelStep = 'choose'" class="px-4 py-2.5 rounded-xl text-xs font-medium text-gray-500 hover:text-gray-300 transition-all">
                                Back
                            </button>
                        </div>
                    </div>

                    {{-- Confirm cancel immediately --}}
                    <div x-show="cancelStep === 'confirm-now'" x-cloak class="space-y-2">
                        <div class="p-3 rounded-xl bg-red-500/5 border border-red-500/10">
                            <p class="text-xs text-red-300 font-medium">Cancel immediately</p>
                            <p class="text-[11px] text-gray-500 mt-1">Your server will be terminated right now and all data will be permanently deleted. All pending invoices will be cancelled.</p>
                        </div>
                        <div class="flex gap-2">
                            <form id="cancel-now-form" method="POST" action="{{ route('client.services.cancel', $service->id) }}" class="flex-1">
                                @csrf
                                <input type="hidden" name="cancel_type" value="immediate">
                                <button type="button" onclick="showConfirm({title: 'Delete Server Immediately', message: 'This will permanently delete your server and ALL data. This cannot be undone. Are you absolutely sure?', type: 'danger', confirmText: 'Yes, Delete Everything', callback: 'cancel-now-form'})" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-red-500 hover:bg-red-400 transition-all">
                                    Confirm Delete
                                </button>
                            </form>
                            <button @click="cancelStep = 'choose'" class="px-4 py-2.5 rounded-xl text-xs font-medium text-gray-500 hover:text-gray-300 transition-all">
                                Back
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
