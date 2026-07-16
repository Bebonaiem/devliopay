@extends('layouts.app')

@section('title', $category->name ?? 'Knowledge Base')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <a href="{{ route('knowledgebase.index') }}" class="hover:text-gray-300 transition-colors">Knowledge Base</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span class="text-gray-300">{{ $category->name ?? 'Category' }}</span>
    </div>

    <h1 class="text-2xl sm:text-3xl font-black tracking-tight mb-2">{{ $category->name ?? 'Knowledge Base' }}</h1>
    <p class="text-sm text-gray-400 mb-8">{{ $category->description ?? 'Browse articles in this category.' }}</p>

    @if(isset($articles) && count($articles) > 0)
        <div class="space-y-3">
            @foreach($articles as $article)
            <div class="glass rounded-2xl p-5 hover:border-brand-500/20 transition-all block group" x-data="{ open: false }">
                <div @click="open = !open" class="flex items-center gap-4 cursor-pointer">
                    <div class="w-10 h-10 rounded-xl bg-brand-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-brand-500/20 transition-colors">
                        <i data-lucide="file-text" class="w-5 h-5 text-brand-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold group-hover:text-brand-400 transition-colors">{{ $article->title }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ Str::limit(strip_tags($article->body ?? $article->content ?? ''), 120) }}</p>
                    </div>
                    <i data-lucide="chevron-right" x-bind:class="open ? 'rotate-90' : ''" class="w-4 h-4 text-gray-600 group-hover:text-brand-400 transition-all flex-shrink-0"></i>
                </div>
                <div x-show="open" x-cloak x-transition class="mt-4 pt-4 border-t border-white/5 text-sm text-gray-300 leading-relaxed prose prose-invert prose-sm max-w-none">
                    {!! $article->body ?? $article->content ?? 'No content available.' !!}
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-12 text-center">
            <p class="text-sm text-gray-400">No articles in this category yet.</p>
        </div>
    @endif
</div>
@endsection
