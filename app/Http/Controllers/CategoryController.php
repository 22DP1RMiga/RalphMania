<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * WEB: Kategorijas lapa
     * GET /shop/category/{slug}
     */
    public function show($slug)
    {
        return Inertia::render('Shop/Category', [
            'slug' => $slug,
        ]);
    }

    /**
     * API: Iegūst visas kategorijas ar REKURSIVISU produktu skaitu
     * GET /api/v1/categories
     *
     * Parent kategorijas (piemēram "Apģērbi") rāda KOPĒJO produktu skaitu —
     * ieskaitot visas apakškategorijas (T-krekli, Džemperi, Cepures utt.).
     * Apakškategorijas rāda tikai savus tiešos produktus.
     */
    public function index()
    {
        // Ielādē visas aktīvās kategorijas ar tiešo produktu skaitu
        $categories = Category::where('is_active', 1)
            ->withCount(['products' => function ($query) {
                $query->where('is_active', 1);
            }])
            ->orderBy('sort_order', 'asc')
            ->get();

        // Aprēķina rekursīvo skaitu parent kategorijām
        // (tiešie produkti + visu bērnu produkti)
        $result = $categories->map(function ($c) use ($categories) {
            $count = $c->products_count;

            // Ja šī ir parent kategorija, pievienot bērnu produktu skaitus
            if (!$c->parent_id) {
                $childIds = $categories
                    ->where('parent_id', $c->id)
                    ->pluck('id');

                foreach ($childIds as $childId) {
                    $child = $categories->firstWhere('id', $childId);
                    if ($child) {
                        $count += $child->products_count;
                    }
                }
            }

            return [
                'id'             => $c->id,
                'name_lv'        => $c->name_lv,
                'name_en'        => $c->name_en,
                'slug'           => $c->slug,
                'description_lv' => $c->description_lv,
                'description_en' => $c->description_en,
                'parent_id'      => $c->parent_id,
                'sort_order'     => $c->sort_order,
                'icon'           => $c->icon ?? null,
                'product_count'  => $count,
            ];
        });

        return response()->json($result->values());
    }

    /**
     * API: Iegūst vienas kategorijas metadatus
     * GET /api/v1/categories/{slug}
     */
    public function apiShow($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        // Rekursīvais skaits arī šeit
        $directCount = Product::where('category_id', $category->id)
            ->where('is_active', 1)
            ->count();

        $childIds = Category::where('parent_id', $category->id)
            ->where('is_active', 1)
            ->pluck('id');

        $childCount = $childIds->isNotEmpty()
            ? Product::whereIn('category_id', $childIds)->where('is_active', 1)->count()
            : 0;

        return response()->json([
            'id'             => $category->id,
            'name_lv'        => $category->name_lv,
            'name_en'        => $category->name_en,
            'slug'           => $category->slug,
            'description_lv' => $category->description_lv,
            'description_en' => $category->description_en,
            'parent_id'      => $category->parent_id,
            'icon'           => $category->icon ?? null,
            'product_count'  => $directCount + $childCount,
        ]);
    }
}
