<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    /**
     * Display the Menahem landing homepage.
     * Content matrix dynamically integrates products from MySQL backend with fallback.
     */
    public function index(): View
    {
        $content = require resource_path('content.php');

        // Dynamically pull latest products from database
        try {
            $dbProducts = Product::where('in_stock', true)->orderBy('sort_order', 'asc')->get();
            if ($dbProducts->isNotEmpty()) {
                $content['CATALOG']['products'] = $dbProducts->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name,
                        'category' => $p->category,
                        'category_slug' => $p->category_slug,
                        'category_slugs' => $p->category_slugs ?: [$p->category_slug],
                        'material' => $p->material,
                        'price' => $p->formatted_price,
                        'numeric_price' => $p->numeric_price,
                        'original_price' => $p->original_price,
                        'rating' => (float) $p->rating,
                        'reviews' => (int) $p->reviews,
                        'badge' => $p->badge,
                        'desc' => $p->desc,
                        'image' => $p->image,
                        'is_featured' => $p->is_featured,
                        'is_bestseller' => $p->is_bestseller,
                    ];
                })->all();
            }
        } catch (\Throwable $e) {
            // Graceful fallback to static content if database is not reachable yet
        }

        return view('landing.index', [
            'content' => $content,
        ]);
    }
}
