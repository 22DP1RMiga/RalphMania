<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * ========================================
     * WEB ROUTES (Inertia Pages)
     * ========================================
     */

    /**
     * WEB: Shop home page
     * GET /shop
     */
    public function shopIndex()
    {
        return Inertia::render('Shop');
    }

    /**
     * WEB: Product detail page
     * GET /shop/product/{slug}
     */
    public function show($slug)
    {
        $product = Product::with('category')
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        return Inertia::render('Shop/ProductDetail', [
            'product' => [
                'id' => $product->id,
                'name_lv' => $product->name_lv,
                'name_en' => $product->name_en,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'description_lv' => $product->description_lv,
                'description_en' => $product->description_en,
                'price' => (float) $product->price,
                'compare_price' => $product->compare_price ? (float) $product->compare_price : null,
                'image' => $product->image,
                'stock_quantity' => $product->stock_quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'is_active' => $product->is_active,
                'is_featured' => $product->is_featured,
                'category_id' => $product->category_id,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name_lv' => $product->category->name_lv,
                    'name_en' => $product->category->name_en,
                    'slug' => $product->category->slug,
                ] : null,
            ]
        ]);
    }

    /**
     * ========================================
     * API ROUTES (JSON)
     * ========================================
     */

    /**
     * API: Get all products with filtering, searching, and sorting
     * GET /api/v1/products?category=1&search=shirt&sort=price-low
     */
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', 1);

        // Filter by category
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_lv', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        switch ($request->get('sort', 'newest')) {
            case 'price-low':
                $query->orderBy('price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->get()->map(fn($p) => [
            'id' => $p->id,
            'name_lv' => $p->name_lv,
            'name_en' => $p->name_en,
            'slug' => $p->slug,
            'sku' => $p->sku,
            'description_lv' => $p->description_lv,
            'description_en' => $p->description_en,
            'price' => (float) $p->price,
            'compare_price' => $p->compare_price ? (float) $p->compare_price : null,
            'image' => $p->image,
            'stock_quantity' => $p->stock_quantity,
            'low_stock_threshold' => $p->low_stock_threshold,
            'is_active' => $p->is_active,
            'is_featured' => $p->is_featured,
            'category_id' => $p->category_id,
            'category' => $p->category ? [
                'id' => $p->category->id,
                'name_lv' => $p->category->name_lv,
                'name_en' => $p->category->name_en,
                'slug' => $p->category->slug,
            ] : null,
        ]);

        return response()->json(['data' => $products]);
    }

    /**
     * API: Get featured products
     * GET /api/v1/products/featured
     */
    public function featured()
    {
        $products = Product::where('is_active', 1)
            ->where('is_featured', 1)
            ->orderBy('created_at', 'desc')
            ->limit(8)
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
            ]);

        return response()->json(['data' => $products]);
    }

    /**
     * API: Get single product (for API use)
     * GET /api/v1/products/{slug}
     */
    public function apiShow($slug)
    {
        $p = Product::with('category')
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        return response()->json([
            'id' => $p->id,
            'name_lv' => $p->name_lv,
            'name_en' => $p->name_en,
            'slug' => $p->slug,
            'sku' => $p->sku,
            'description_lv' => $p->description_lv,
            'description_en' => $p->description_en,
            'price' => (float) $p->price,
            'compare_price' => $p->compare_price ? (float) $p->compare_price : null,
            'image' => $p->image,
            'stock_quantity' => $p->stock_quantity,
            'low_stock_threshold' => $p->low_stock_threshold,
            'is_active' => $p->is_active,
            'category_id' => $p->category_id,
            'category' => $p->category ? [
                'id' => $p->category->id,
                'name_lv' => $p->category->name_lv,
                'name_en' => $p->category->name_en,
            ] : null,
        ]);
    }

    /**
     * ========================================
     * ADMIN METHODS (TODO)
     * ========================================
     */

    /**
     * Admin: List all products
     */
    public function adminIndex()
    {
        // TODO: Implement admin product list
        return Inertia::render('Admin/Products/Index');
    }

    /**
     * Admin: Create product form
     */
    public function create()
    {
        // TODO: Implement product creation
        return Inertia::render('Admin/Products/Create');
    }

    /**
     * Admin: Store new product
     */
    public function store(Request $request)
    {
        // TODO: Implement product storage
    }

    /**
     * Admin: Edit product form
     */
    public function edit($id)
    {
        // TODO: Implement product editing
        return Inertia::render('Admin/Products/Edit');
    }

    /**
     * Admin: Update product
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement product update
    }

    /**
     * Admin: Delete product
     */
    public function destroy($id)
    {
        // TODO: Implement product deletion
    }
}
