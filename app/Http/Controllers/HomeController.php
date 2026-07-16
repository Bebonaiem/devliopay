<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->with('products', function ($query) {
                $query->where('is_active', true)
                    ->where('is_hidden', false)
                    ->with(['pricing.currencies'])
                    ->orderBy('sort_order');
            })
            ->orderBy('order')
            ->get();

        $announcements = Announcement::where('status', 'published')
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('home', compact('categories', 'announcements'));
    }
}
