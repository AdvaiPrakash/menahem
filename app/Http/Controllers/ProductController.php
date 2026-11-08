<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display the Menahem Product List Page.
     * Matches the reference e-commerce catalog layout.
     */
    public function index(Request $request): View
    {
        $content = require resource_path('content.php');
        $catalog = $content['CATALOG'] ?? [];
        $selectedCategory = $request->query('category', 'all');

        return view('products.index', [
            'content' => $content,
            'catalog' => $catalog,
            'selectedCategory' => $selectedCategory,
        ]);
    }

    /**
     * Display the Detailed Product Page.
     * Shows full gallery, specifications, care notes, quantity & WhatsApp buy action.
     */
    public function show(string|int $id): View
    {
        $content = require resource_path('content.php');
        $catalog = $content['CATALOG'] ?? [];
        $allProducts = $catalog['products'] ?? [];

        // Search product by numeric id
        $product = collect($allProducts)->first(function ($item) use ($id) {
            return (string) ($item['id'] ?? '') === (string) $id;
        });

        // Fallback to first product if not found to prevent broken experience
        if (! $product) {
            $product = $allProducts[0] ?? null;
        }

        if (! $product) {
            abort(404, 'Product not found');
        }

        // Get 4 related products (excluding current one)
        $relatedProducts = collect($allProducts)
            ->filter(function ($item) use ($product) {
                return ($item['id'] ?? 0) !== ($product['id'] ?? 0);
            })
            ->take(4)
            ->values()
            ->all();

        return view('products.show', [
            'content' => $content,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
