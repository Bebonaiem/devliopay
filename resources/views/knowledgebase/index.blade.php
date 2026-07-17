@extends('layouts.app')

@section('title', 'Knowledge Base')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-12">
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight mb-3">Knowledge Base</h1>
        <p class="text-gray-400 max-w-lg mx-auto">Find answers to common questions and helpful guides.</p>
    </div>

    {{-- Search --}}
    <form action="{{ route('knowledgebase.index') }}" method="GET" class="max-w-xl mx-auto mb-10">
        <div class="relative">
            <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..." class="w-full bg-white/[0.03] border border-white/10 rounded-2xl pl-12 pr-4 py-4 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 focus:ring-1 focus:ring-brand-500/20">
        </div>
    </form>

    {{-- Categories --}}
    @if(isset($categories) && count($categories) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($categories as $cat)
            <a href="{{ route('knowledgebase.show', $cat->category ?? $cat->slug ?? '') }}" class="glass rounded-2xl p-6 hover:border-brand-500/20 transition-all group card-hover">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/10 flex items-center justify-center group-hover:bg-brand-500/20 transition-colors">
                        <i data-lucide="folder" class="w-6 h-6 text-brand-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-bold group-hover:text-brand-400 transition-colors">{{ $cat->category ?? $cat->slug ?? 'Uncategorized' }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $cat->articles_count ?? 0 }} articles</p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-600 group-hover:text-brand-400 transition-colors"></i>
                </div>
            </a>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="book-open" class="w-8 h-8 text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2">No articles yet</h3>
            <p class="text-sm text-gray-400">Knowledge base articles will appear here soon.</p>
        </div>
    @endif
</div>
@endsection
