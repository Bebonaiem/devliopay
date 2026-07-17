@extends('layouts.app')

@section('title', $announcement->title ?? 'Announcement')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <a href="{{ route('announcements.index') }}" class="hover:text-gray-300 transition-colors">Announcements</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-300 truncate">{{ $announcement->title ?? 'Announcement' }}</span>
    </div>

    <article class="glass rounded-2xl p-8">
        @if($announcement->image)
            <div class="mb-6 rounded-xl overflow-hidden">
                <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="w-full h-auto rounded-xl">
            </div>
        @endif
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight mb-4">{{ $announcement->title }}</h1>
        <div class="flex items-center gap-4 text-xs text-gray-500 mb-6 pb-6 border-b border-white/5">
            <span><i data-lucide="calendar" class="w-3 h-3 inline mr-0.5"></i>{{ $announcement->created_at->format('M j, Y') }}</span>
            <span><i data-lucide="user" class="w-3 h-3 inline mr-0.5"></i>{{ $announcement->user->name ?? 'Admin' }}</span>
        </div>
        <div class="text-sm text-gray-300 leading-relaxed prose prose-invert prose-sm max-w-none">
            {!! $announcement->content ?? '' !!}
        </div>
    </article>
</div>
@endsection
