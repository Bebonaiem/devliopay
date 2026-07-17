@extends('layouts.app')

@section('title', 'Two-Factor Authentication')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="absolute inset-0 hero-gradient"></div>
    <div class="absolute top-1/3 left-1/2 -translate-x-1/2 w-96 h-48 bg-brand-500/10 rounded-full blur-[120px]"></div>

    <div class="relative w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center shadow-lg shadow-brand-500/25">
                    <i data-lucide="zap" class="w-5 h-5 text-white"></i>
                </div>
            </a>
            <h1 class="text-2xl font-bold tracking-tight">Two-Factor Authentication</h1>
            <p class="text-sm text-gray-400 mt-1">Enter the 6-digit code from your authenticator app</p>
        </div>

        <form method="POST" action="{{ route('two-factor.challenge.verify') }}" class="glass rounded-2xl p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Verification Code</label>
                <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" required autofocus class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 text-center font-mono text-2xl tracking-[0.5em] placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="000000" autocomplete="one-time-code">
                @error('code') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-bold text-white shadow-xl shadow-brand-500/25">
                <i data-lucide="shield-check" class="w-4 h-4 inline mr-2"></i> Verify
            </button>
        </form>

        <p class="text-center text-sm text-gray-400 mt-6">
            <a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300 font-semibold transition-colors">Back to Login</a>
        </p>
    </div>
</div>
@endsection
