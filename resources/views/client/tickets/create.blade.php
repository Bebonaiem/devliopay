@extends('layouts.client')

@section('title', 'Open New Ticket')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{ priority: '{{ old('priority', 'medium') }}' }">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('client.tickets.index') }}" class="hover:text-gray-300 transition-colors">Tickets</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-300">New Ticket</span>
    </div>

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Open New Ticket</h1>
        <p class="text-sm text-gray-400 mt-1">Describe your issue and we'll get back to you shortly.</p>
    </div>

    <form method="POST" action="{{ route('client.tickets.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- Subject --}}
        <div class="glass rounded-2xl p-5 space-y-4">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center">
                    <i data-lucide="type" class="w-4 h-4 text-brand-400"></i>
                </div>
                <h2 class="text-sm font-semibold">Ticket Details</h2>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required
                    class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 transition-all"
                    placeholder="Brief description of your issue">
                @error('subject') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if(isset($departments) && count($departments) > 0)
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Department</label>
                    <select name="department_id" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 appearance-none transition-all">
                        <option value="">Select department</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif

                @if(isset($services) && count($services) > 0)
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Related Service</label>
                    <select name="service_id" class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 appearance-none transition-all">
                        <option value="">None</option>
                        @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->product->name ?? 'Service' }}</option>
                        @endforeach
                    </select>
                    @error('service_id') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
            </div>
        </div>

        {{-- Priority --}}
        <div class="glass rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center">
                    <i data-lucide="flag" class="w-4 h-4 text-brand-400"></i>
                </div>
                <h2 class="text-sm font-semibold">Priority</h2>
            </div>
            <div class="grid grid-cols-4 gap-2">
                @foreach(['low' => ['Low', 'text-gray-400', 'bg-gray-500/10 border-gray-500/20', 'bg-gray-500/15 text-gray-300 border-gray-500/30'], 'medium' => ['Medium', 'text-blue-400', 'bg-blue-500/10 border-blue-500/20', 'bg-blue-500/15 text-blue-300 border-blue-500/30'], 'high' => ['High', 'text-orange-400', 'bg-orange-500/10 border-orange-500/20', 'bg-orange-500/15 text-orange-300 border-orange-500/30'], 'urgent' => ['Urgent', 'text-red-400', 'bg-red-500/10 border-red-500/20', 'bg-red-500/15 text-red-300 border-red-500/30']] as $value => $info)
                <label class="cursor-pointer">
                    <input type="radio" name="priority" value="{{ $value }}" x-model="priority" class="sr-only">
                    <div class="px-3 py-3 rounded-xl text-xs font-semibold text-center border transition-all text-center"
                        :class="priority === '{{ $value }}' ? '{{ $info[3] }}' : '{{ $info[2] }} {{ $info[1] }}'">
                        {{ $info[0] }}
                    </div>
                </label>
                @endforeach
            </div>
            @error('priority') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Message --}}
        <div class="glass rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center">
                    <i data-lucide="message-square" class="w-4 h-4 text-brand-400"></i>
                </div>
                <h2 class="text-sm font-semibold">Message</h2>
            </div>
            <textarea name="message" rows="8" required
                class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20 resize-none transition-all"
                placeholder="Describe your issue in detail...">{{ old('message') }}</textarea>
            @error('message') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Attachments --}}
        <div class="glass rounded-2xl p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-lg bg-brand-500/15 flex items-center justify-center">
                    <i data-lucide="paperclip" class="w-4 h-4 text-brand-400"></i>
                </div>
                <h2 class="text-sm font-semibold">Attachments</h2>
                <span class="text-[10px] text-gray-500 font-normal">Optional</span>
            </div>
            <input type="file" name="attachments[]" multiple
                class="w-full text-xs text-gray-400 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 file:cursor-pointer transition-all"
                accept=".jpg,.jpeg,.png,.gif,.pdf,.txt,.zip">
            <p class="text-[10px] text-gray-500 mt-2">Max 5 files, 10MB each. JPG, PNG, GIF, PDF, TXT, ZIP</p>
            @error('attachments') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
            @error('attachments.*') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('client.tickets.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-medium text-gray-400 hover:text-gray-300 transition-all inline-flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Tickets
            </a>
            <button type="submit" class="btn-primary px-6 py-2.5 rounded-xl text-xs font-semibold text-white shadow-lg shadow-brand-500/20 inline-flex items-center gap-2">
                <i data-lucide="send" class="w-3.5 h-3.5"></i> Submit Ticket
            </button>
        </div>
    </form>
</div>
@endsection
