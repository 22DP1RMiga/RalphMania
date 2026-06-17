<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Content;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Parāda sākumlapu ar piedāvātajiem produktiem un saturu
     */
    public function index(): Response
    {
        // Iegūst 3 piedāvātos (featured) produktus
        $featuredProducts = Product::where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($product) {
                $vatRate = \App\Models\Product::vatRate();
                return [
                    'id'            => $product->id,
                    'slug'          => $product->slug,
                    'name_lv'       => $product->name_lv,
                    'name_en'       => $product->name_en,
                    'description_lv'=> $product->description_lv,
                    'description_en'=> $product->description_en,
                    'price'         => (float) $product->price,
                    'compare_price' => $product->compare_price ? (float) $product->compare_price : null,
                    'price_ex_vat'  => $product->price_ex_vat,
                    'vat_amount'    => $product->vat_amount,
                    'vat_rate'      => $vatRate,
                    'image'         => $product->image,
                    'is_featured'   => $product->is_featured,
                    'has_sizes'     => (bool) $product->has_sizes,
                    'stock_quantity'=> $product->stock_quantity,
                ];
            });

        // Iegūst 3 piedāvātos (featured) satura materiālus
        $featuredContent = Content::where('is_featured', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($content) {
                return [
                    'id' => $content->id,
                    'slug' => $content->slug,
                    'title_lv' => $content->title_lv,
                    'title_en' => $content->title_en,
                    'description_lv' => $content->description_lv,
                    'description_en' => $content->description_en,
                    'thumbnail' => $content->thumbnail,
                    'type' => $content->type,
                    'category' => $content->category,
                    'video_platform' => $content->video_platform,
                    'view_count' => $content->view_count ?? 0,
                    'like_count' => $content->like_count ?? 0,
                    'published_at' => $content->published_at,
                ];
            });

        return Inertia::render('Home', [
            'featuredProducts' => $featuredProducts,
            'featuredContent' => $featuredContent,
        ]);
    }
}
