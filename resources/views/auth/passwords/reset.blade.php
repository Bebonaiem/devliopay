@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="glass rounded-2xl p-8 border border-white/5">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold tracking-tight">Reset Password</h1>
                <p class="text-sm text-gray-400 mt-2">Enter your new password below</p>
            </div>

            @if($errors->any())
                <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm mb-6">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">New Password</label>
                        <input type="password" name="password" required autofocus
                            class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 transition-all">
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand-500 hover:bg-brand-400 transition-all">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
