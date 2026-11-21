<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display the Menahem Product List Page.
     * Matches the reference e-commerce catalog layout and pulls from MySQL.
     */
    public function index(Request $request): View
    {
        $content = require resource_path('content.php');
        $catalog = $content['CATALOG'] ?? [];

        // Augment with database products if available
        try {
            $dbProducts = Product::where('in_stock', true)->orderBy('sort_order', 'asc')->get();
            if ($dbProducts->isNotEmpty()) {
                $catalog['products'] = $dbProducts->map(function ($p) {
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
                    ];
                })->all();
            }
        } catch (\Throwable $e) {
            // Keep content.php fallback
        }

        $selectedCategory = $request->query('category', 'all');

        $categoryInfo = null;
        $breadcrumbs = null;
        $parentBackUrl = route('categories.index');
        $parentBackName = 'Shop by Category';
        $targetCategorySlugs = [$selectedCategory];

        if ($selectedCategory !== 'all') {
            $resolved = CategoryService::findBySlug($selectedCategory);

            if ($resolved) {
                $categoryInfo = $resolved['node'];
                $breadcrumbs = CategoryService::getBreadcrumbs($resolved);
                $targetCategorySlugs = CategoryService::getAllLeafSlugs($categoryInfo);
                $targetCategorySlugs[] = $selectedCategory;

                if (! empty($categoryInfo['subcategories'])) {
                    // Category has its own subcategory overview page
                    $parentBackName = $categoryInfo['name'];
                    $parentBackUrl = url('/categories/'.$resolved['path']);
                } elseif (! empty($resolved['parent']) && ! empty($resolved['parent_path'])) {
                    // Leaf category: go back to its parent category overview page
                    $parentBackName = $resolved['parent']['name'];
                    $parentBackUrl = url('/categories/'.$resolved['parent_path']);
                }
            }
        }

        return view('products.index', [
            'content' => $content,
            'catalog' => $catalog,
            'selectedCategory' => $selectedCategory,
            'categoryInfo' => $categoryInfo,
            'breadcrumbs' => $breadcrumbs,
            'parentBackUrl' => $parentBackUrl,
            'parentBackName' => $parentBackName,
            'targetCategorySlugs' => $targetCategorySlugs,
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

        // Try resolving from database first
        $product = null;
        try {
            $dbProduct = Product::find($id);
            if ($dbProduct) {
                $product = [
                    'id' => $dbProduct->id,
                    'name' => $dbProduct->name,
                    'category' => $dbProduct->category,
                    'category_slug' => $dbProduct->category_slug,
                    'category_slugs' => $dbProduct->category_slugs ?: [$dbProduct->category_slug],
                    'material' => $dbProduct->material,
                    'price' => $dbProduct->formatted_price,
                    'numeric_price' => $dbProduct->numeric_price,
                    'original_price' => $dbProduct->original_price,
                    'rating' => (float) $dbProduct->rating,
                    'reviews' => (int) $dbProduct->reviews,
                    'badge' => $dbProduct->badge,
                    'desc' => $dbProduct->desc,
                    'image' => $dbProduct->image,
                ];
            }
        } catch (\Throwable $e) {
            // Keep fallback
        }

        // Fallback to searching in array if not found in DB
        if (! $product) {
            $product = collect($allProducts)->first(function ($item) use ($id) {
                return (string) ($item['id'] ?? '') === (string) $id;
            });
        }

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
