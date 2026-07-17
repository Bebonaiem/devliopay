@extends('layouts.client')

@section('title', 'Profile Settings')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Profile Settings</h1>
        <p class="text-sm text-gray-400 mt-1">Manage your account information.</p>
    </div>

    <form method="POST" action="{{ route('client.profile.update') }}" class="glass rounded-2xl p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Full Name</label>
            <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
            @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Email Address</label>
            <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
            @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Phone Number</label>
            <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="+1 (555) 000-0000">
            @error('phone') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Company</label>
                <input type="text" name="company" value="{{ old('company', auth()->user()->company ?? '') }}" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="Company name (optional)">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Country</label>
                <input type="text" name="country" value="{{ old('country', auth()->user()->country ?? '') }}" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="United States">
                @error('country') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Address</label>
            <input type="text" name="address" value="{{ old('address', auth()->user()->address ?? '') }}" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20" placeholder="Street address">
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">City</label>
                <input type="text" name="city" value="{{ old('city', auth()->user()->city ?? '') }}" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
                @error('city') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">State</label>
                <input type="text" name="state" value="{{ old('state', auth()->user()->state ?? '') }}" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
                @error('state') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Zip Code</label>
                <input type="text" name="zip_code" value="{{ old('zip_code', auth()->user()->zip_code ?? '') }}" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
                @error('zip_code') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center justify-end pt-2">
            <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
                <i data-lucide="save" class="w-3.5 h-3.5"></i> Save Changes
            </button>
        </div>
    </form>

    {{-- Change Password --}}
    <form method="POST" action="{{ route('client.profile.password') }}" class="glass rounded-2xl p-6 space-y-5">
        @csrf
        @method('PUT')
        <h2 class="text-sm font-semibold">Change Password</h2>

        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Current Password</label>
            <input type="password" name="current_password" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">New Password</label>
                <input type="password" name="password" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
            </div>
        </div>

        <div class="flex items-center justify-end pt-2">
            <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-semibold text-white bg-white/10 hover:bg-white/15 border border-white/10 transition-all inline-flex items-center gap-2">
                <i data-lucide="lock" class="w-3.5 h-3.5"></i> Update Password
            </button>
        </div>
    </form>
</div>
@endsection
