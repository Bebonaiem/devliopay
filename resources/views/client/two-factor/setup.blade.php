@extends('layouts.client')

@section('title', 'Setup Two-Factor Authentication')

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('client.two-factor.index') }}" class="hover:text-gray-300 transition-colors">2FA</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-300">Setup</span>
    </div>

    <div>
        <h1 class="text-2xl font-bold tracking-tight">Setup 2FA</h1>
        <p class="text-sm text-gray-400 mt-1">Scan the QR code with your authenticator app.</p>
    </div>

    <div class="glass rounded-2xl p-6 text-center space-y-6">
        @if(isset($qrCode))
        <div class="inline-block p-4 bg-white rounded-2xl">{!! $qrCode !!}</div>
        @else
        <div class="w-48 h-48 mx-auto bg-white/5 rounded-2xl flex items-center justify-center">
            <i data-lucide="qr-code" class="w-16 h-16 text-gray-600"></i>
        </div>
        @endif

        @if(isset($secret))
        <div class="p-4 rounded-xl bg-white/[0.02] border border-white/5">
            <p class="text-[11px] text-gray-500 uppercase tracking-wider mb-2">Manual Entry Key</p>
            <p class="text-sm font-mono text-gray-200 tracking-wider select-all">{{ $secret }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('client.two-factor.confirm') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Verification Code</label>
                <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 text-center font-mono text-2xl tracking-[0.5em] placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="000000" autofocus>
                @error('code') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Your Password</label>
                <input type="password" name="password" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="Enter your password">
                @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-semibold text-white shadow-lg shadow-brand-500/20">
                <i data-lucide="shield-check" class="w-4 h-4 inline mr-2"></i> Enable 2FA
            </button>
        </form>
    </div>
</div>
@endsection
