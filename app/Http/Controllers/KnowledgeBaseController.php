<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request)
    {
        $query = KnowledgeBaseArticle::published()->orderBy('sort_order');

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        $articles = $query->get();
        $categories = KnowledgeBaseArticle::published()
            ->select('category')
            ->distinct()
            ->pluck('category');

        return view('knowledgebase.index', compact('articles', 'categories'));
    }

    public function show(string $slug)
    {
        $article = KnowledgeBaseArticle::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $article->incrementViews();

        return view('knowledgebase.show', compact('article'));
    }
}
