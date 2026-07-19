<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $articles = KnowledgeBaseArticle::published()
            ->orderBy('sort_order')
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->get();

        $categories = KnowledgeBaseArticle::published()
            ->selectRaw('category, COUNT(*) as articles_count')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return view('knowledgebase.index', compact('articles', 'categories'));
    }

    public function show(string $slug)
    {
        $article = KnowledgeBaseArticle::published()
            ->where('slug', $slug)
            ->first();

        if ($article) {
            $article->incrementViews();

            return view('knowledgebase.show', compact('article'));
        }

        // Fallback: treat as category name (list articles in category)
        $articles = KnowledgeBaseArticle::published()
            ->where('category', $slug)
            ->orderBy('sort_order')
            ->get();

        if ($articles->isEmpty()) {
            abort(404);
        }

        $category = (object) [
            'name' => $slug,
            'slug' => $slug,
            'articles_count' => $articles->count(),
        ];

        return view('knowledgebase.show', compact('category', 'articles'));
    }
}
