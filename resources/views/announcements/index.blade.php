@extends('layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-3">Announcements</h1>
        <p class="text-gray-400">Stay up to date with the latest news and updates.</p>
    </div>

    @if(isset($announcements) && count($announcements) > 0)
        <div class="space-y-4">
            @foreach($announcements as $announcement)
            <a href="{{ route('announcements.show', $announcement->slug) }}" class="glass rounded-2xl p-6 hover:border-brand-500/20 transition-all block group">
                @if($announcement->image)
                    <div class="mb-4 rounded-xl overflow-hidden">
                        <img src="{{ asset('storage/' . $announcement->image) }}" alt="{{ $announcement->title }}" class="w-full h-48 object-cover">
                    </div>
                @endif
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-bold group-hover:text-brand-400 transition-colors mb-1">{{ $announcement->title }}</h2>
                        <p class="text-sm text-gray-400 line-clamp-2 mb-2">{{ Str::limit(strip_tags($announcement->content ?? ''), 180) }}</p>
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span><i data-lucide="calendar" class="w-3 h-3 inline mr-0.5"></i>{{ $announcement->created_at->format('M j, Y') }}</span>
                            <span><i data-lucide="user" class="w-3 h-3 inline mr-0.5"></i>{{ $announcement->user->name ?? 'Admin' }}</span>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-600 group-hover:text-brand-400 transition-colors flex-shrink-0 mt-1"></i>
                </div>
            </a>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="megaphone" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">No announcements</h3>
            <p class="text-sm text-gray-400">Check back soon for updates!</p>
        </div>
    @endif
</div>
@endsection
