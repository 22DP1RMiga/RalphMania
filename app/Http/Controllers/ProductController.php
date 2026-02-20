<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
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
            'product' => $this->formatProduct($product),
        ]);
    }

    /**
     * API: Get all products
     * GET /api/v1/products
     */
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', 1);

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_lv', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        switch ($request->get('sort', 'newest')) {
            case 'price-low':  $query->orderBy('price', 'asc'); break;
            case 'price-high': $query->orderBy('price', 'desc'); break;
            case 'popular':    $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc'); break;
            default:           $query->orderBy('created_at', 'desc');
        }

        $products = $query->get()->map(fn($p) => $this->formatProduct($p));

        return response()->json(['data' => $products]);
    }

    /**
     * API: Featured products
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
                'id'            => $p->id,
                'name_lv'       => $p->name_lv,
                'name_en'       => $p->name_en,
                'slug'          => $p->slug,
                'price'         => (float) $p->price,
                'compare_price' => $p->compare_price ? (float) $p->compare_price : null,
                'image'         => $p->image,
                'stock_quantity'=> $p->stock_quantity,
                'has_sizes'     => (bool) $p->has_sizes,
            ]);

        return response()->json(['data' => $products]);
    }

    /**
     * API: Single product
     * GET /api/v1/products/{slug}
     */
    public function apiShow($slug)
    {
        $p = Product::with('category')
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        return response()->json($this->formatProduct($p));
    }

    // ─── PRIVATE HELPER ──────────────────────────────────────────────────────

    /**
     * Standarts produkta masīvs visām atbildēm.
     * Iekļauj has_sizes lai frontend zina vai rādīt izmēru izvēlni.
     */
    private function formatProduct(Product $p): array
    {
        return [
            'id'                  => $p->id,
            'name_lv'             => $p->name_lv,
            'name_en'             => $p->name_en,
            'slug'                => $p->slug,
            'sku'                 => $p->sku,
            'description_lv'      => $p->description_lv,
            'description_en'      => $p->description_en,
            'price'               => (float) $p->price,
            'compare_price'       => $p->compare_price ? (float) $p->compare_price : null,
            'image'               => $p->image,
            'stock_quantity'      => $p->stock_quantity,
            'low_stock_threshold' => $p->low_stock_threshold,
            'is_active'           => $p->is_active,
            'is_featured'         => $p->is_featured,
            'has_sizes'           => (bool) $p->has_sizes,  // ← JAUNS
            'category_id'         => $p->category_id,
            'category'            => $p->category ? [
                'id'      => $p->category->id,
                'name_lv' => $p->category->name_lv,
                'name_en' => $p->category->name_en,
                'slug'    => $p->category->slug,
            ] : null,
        ];
    }
}
