@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="glass rounded-2xl p-8 border border-white/5">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold tracking-tight">Reset Password</h1>
                <p class="text-sm text-gray-400 mt-2">Enter your email to receive a reset link</p>
            </div>

            @if(session('status'))
                <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center mb-6">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm mb-6">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 transition-all">
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-500 hover:bg-brand-400 transition-all">
                        Send Reset Link
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-xs text-gray-400 hover:text-white transition-colors">
                    Back to login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
