<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)
            ->where('is_hidden', false)
            ->with(['category', 'pricing.currencies']);

        if ($request->category) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        $products = $query->orderBy('sort_order')->get();
        $categories = Category::where('is_active', true)->orderBy('order')->get();

        return view('store.index', compact('products', 'categories'));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'pricing.currencies', 'configOptions'])
            ->firstOrFail();

        return view('store.show', compact('product'));
    }
}
