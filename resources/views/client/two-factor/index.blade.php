@extends('layouts.client')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Two-Factor Authentication</h1>
        <p class="text-sm text-gray-400 mt-1">Add an extra layer of security to your account.</p>
    </div>

    <div class="glass rounded-2xl p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-xl {{ ($enabled ?? false) ? 'bg-emerald-500/10' : 'bg-white/5' }} flex items-center justify-center">
                <i data-lucide="shield" class="w-6 h-6 {{ ($enabled ?? false) ? 'text-emerald-400' : 'text-gray-500' }}"></i>
            </div>
            <div>
                <h2 class="text-sm font-semibold">Status: <span class="{{ ($enabled ?? false) ? 'text-emerald-400' : 'text-gray-400' }}">{{ ($enabled ?? false) ? 'Enabled' : 'Disabled' }}</span></h2>
                <p class="text-xs text-gray-500">{{ ($enabled ?? false) ? 'Your account is protected with 2FA.' : 'Enable 2FA to secure your account.' }}</p>
            </div>
        </div>

        @if(!($enabled ?? false))
        <div class="space-y-4">
            <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5">
                <h3 class="text-sm font-semibold mb-2">How it works</h3>
                <ul class="text-xs text-gray-400 space-y-2">
                    <li class="flex items-start gap-2"><i data-lucide="check" class="w-3.5 h-3.5 text-brand-400 mt-0.5 flex-shrink-0"></i>Download an authenticator app (Google Authenticator, Authy, etc.)</li>
                    <li class="flex items-start gap-2"><i data-lucide="check" class="w-3.5 h-3.5 text-brand-400 mt-0.5 flex-shrink-0"></i>Scan the QR code or enter the secret key</li>
                    <li class="flex items-start gap-2"><i data-lucide="check" class="w-3.5 h-3.5 text-brand-400 mt-0.5 flex-shrink-0"></i>Enter the 6-digit code to verify and activate</li>
                </ul>
            </div>
            <a href="{{ route('client.two-factor.show-setup') }}" class="btn-primary px-6 py-3 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
                <i data-lucide="shield-plus" class="w-4 h-4"></i> Enable 2FA
            </a>
        </div>
        @else
        <div class="space-y-4">
            <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-500/5 border border-emerald-500/10">
                <i data-lucide="shield-check" class="w-5 h-5 text-emerald-400"></i>
                <p class="text-sm text-gray-300">Two-factor authentication is currently active on your account.</p>
            </div>
            <form id="disable-2fa-form" method="POST" action="{{ route('client.two-factor.disable') }}">
                @csrf
                @method('DELETE')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Confirm Password</label>
                        <input type="password" name="password" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="Enter your password">
                        @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" onclick="return confirm('Are you sure you want to disable two-factor authentication? Your account will be less secure.')" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-red-400 bg-red-500/5 hover:bg-red-500/10 border border-red-500/10 hover:border-red-500/20 transition-all inline-flex items-center gap-2">
                        <i data-lucide="shield-off" class="w-3.5 h-3.5"></i> Disable 2FA
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
