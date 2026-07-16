@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="absolute inset-0 hero-gradient"></div>
    <div class="absolute top-1/3 left-1/2 -translate-x-1/2 w-96 h-48 bg-brand-500/10 rounded-full blur-[120px]"></div>

    <div class="relative w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center shadow-lg shadow-brand-500/25">
                    <i data-lucide="zap" class="w-5 h-5 text-white"></i>
                </div>
            </a>
            <h1 class="text-2xl font-bold tracking-tight">Welcome back</h1>
            <p class="text-sm text-gray-400 mt-1">Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="glass rounded-2xl p-6 space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="you@example.com">
                @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Password</label>
                    <a href="{{ route('password.request') }}" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">Forgot password?</a>
                </div>
                <input type="password" name="password" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="Enter your password">
                @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-500/20">
                <span class="text-sm text-gray-400">Remember me</span>
            </label>

            <button type="submit" class="btn-primary w-full py-3 rounded-xl text-sm font-bold text-white shadow-xl shadow-brand-500/25">
                Sign In
            </button>
        </form>

        <p class="text-center text-sm text-gray-400 mt-6">
            Don't have an account? <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300 font-semibold transition-colors">Create Account</a>
        </p>
    </div>
</div>
@endsection
