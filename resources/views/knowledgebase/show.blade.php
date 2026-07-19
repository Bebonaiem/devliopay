@extends('layouts.app')

@section('title', isset($article) ? $article->title : ($category->name ?? 'Knowledge Base'))

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-8">
        <a href="{{ route('knowledgebase.index') }}" class="hover:text-gray-300 transition-colors">Knowledge Base</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        @if(isset($article))
            <span class="text-gray-300">{{ $article->category }}</span>
        @else
            <span class="text-gray-300">{{ $category->name ?? 'Category' }}</span>
        @endif
    </div>

    @if(isset($article))
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight mb-4">{{ $article->title }}</h1>
        <div class="text-sm text-gray-500 mb-8 flex items-center gap-4">
            <span>Category: {{ $article->category }}</span>
        </div>
        <div class="glass rounded-2xl p-6 sm:p-8 text-sm text-gray-300 leading-relaxed prose prose-invert prose-sm max-w-none">
            {!! $article->content !!}
        </div>
        <div class="mt-8 text-center">
            <a href="{{ route('knowledgebase.index') }}" class="text-sm text-brand-400 hover:text-brand-300 transition-colors">&larr; Back to Knowledge Base</a>
        </div>
    @elseif(isset($articles) && count($articles) > 0)
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight mb-2">{{ $category->name ?? 'Knowledge Base' }}</h1>
        <p class="text-sm text-gray-400 mb-8">{{ $category->articles_count ?? 0 }} articles in this category.</p>
        <div class="space-y-3">
            @foreach($articles as $article)
            <a href="{{ route('knowledgebase.show', $article->slug) }}"
               class="block glass rounded-2xl p-5 hover:border-brand-500/20 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-brand-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-brand-500/20 transition-colors">
                        <i data-lucide="file-text" class="w-5 h-5 text-brand-400"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-semibold group-hover:text-brand-400 transition-colors">{{ $article->title }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ Str::limit(strip_tags($article->content ?? ''), 120) }}</p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-gray-600 group-hover:text-brand-400 transition-all flex-shrink-0"></i>
                </div>
            </a>
            @endforeach
        </div>
    @else
        <div class="glass rounded-2xl px-6 py-12 text-center">
            <p class="text-sm text-gray-400">No articles in this category yet.</p>
        </div>
    @endif
</div>
@endsection
