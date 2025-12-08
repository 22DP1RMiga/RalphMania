<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * API: Get all categories with product count
     * GET /api/v1/categories
     */
    public function index()
    {
        $categories = Category::where('is_active', 1)
            ->withCount(['products' => function ($query) {
                $query->where('is_active', 1);
            }])
            ->orderBy('sort_order', 'asc')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name_lv' => $c->name_lv,
                'name_en' => $c->name_en,
                'slug' => $c->slug,
                'description_lv' => $c->description_lv,
                'description_en' => $c->description_en,
                'icon' => $c->icon ?? 'fas fa-tag',
                'product_count' => $c->products_count,
            ]);

        return response()->json($categories);
    }

    /**
     * API: Get category with products
     * GET /api/v1/categories/{slug}
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        $products = Product::where('category_id', $category->id)
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name_lv' => $p->name_lv,
                'name_en' => $p->name_en,
                'slug' => $p->slug,
                'price' => (float) $p->price,
                'compare_price' => $p->compare_price ? (float) $p->compare_price : null,
                'image' => $p->image,
                'stock_quantity' => $p->stock_quantity,
                'low_stock_threshold' => $p->low_stock_threshold,
            ]);

        return response()->json([
            'id' => $category->id,
            'name_lv' => $category->name_lv,
            'name_en' => $category->name_en,
            'slug' => $category->slug,
            'products' => $products,
        ]);
    }
}
