<?php

namespace App\Http\Controllers;

use App\Models\ClearanceItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
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

        // Dynamically pull latest products & clearance items from database
        try {
            $hasClearanceTable = ClearanceItem::tableExists();
            $dbProductsQuery = Product::query()
                ->where('in_stock', true)
                ->orderBy('sort_order', 'asc');
            if ($hasClearanceTable) {
                $dbProductsQuery->with('clearanceItem');
            }
            $dbProducts = $dbProductsQuery->get();

            if ($dbProducts->isNotEmpty()) {
                $content['CATALOG']['products'] = $dbProducts
                    ->filter(static function (Product $p): bool {
                        $c = $p->clearanceItem;

                        return ! ($c && $c->is_active && ! $c->show_in_all_products);
                    })
                    ->map(function (Product $p): array {
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'category' => $p->category,
                            'category_slug' => $p->category_slug,
                            'category_slugs' => $p->category_slugs ?: [$p->category_slug],
                            'material' => $p->material,
                            'price' => $p->effectiveFormattedPrice(),
                            'numeric_price' => $p->effectivePrice(),
                            'original_price' => $p->effectiveOriginalPrice(),
                            'rating' => (float) $p->rating,
                            'reviews' => (int) $p->reviews,
                            'badge' => $p->badge,
                            'desc' => $p->desc,
                            'image' => $p->image,
                            'is_featured' => $p->is_featured,
                            'is_bestseller' => $p->is_bestseller,
                        ];
                    })
                    ->values()
                    ->all();
            }

            if (Schema::hasTable('clearance_items')) {
                $isManaged = ClearanceItem::exists() || Setting::get('clearance_sale_managed') === '1';
                if ($isManaged) {
                    $clearanceItems = ClearanceItem::with('product')
                        ->where('is_active', true)
                        ->where('show_on_homepage', true)
                        ->whereHas('product', static fn ($q) => $q->where('in_stock', true))
                        ->orderBy('display_order')
                        ->orderBy('id')
                        ->get();

                    $content['CLEARANCE_SALE']['products'] = $clearanceItems->map(static function (ClearanceItem $item): array {
                        $p = $item->product;
                        $salePrice = $item->calculateSalePrice($p->numeric_price);

                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'category' => $p->category,
                            'price' => '₹'.number_format($salePrice, fmod($salePrice, 1.0) === 0.0 ? 0 : 2),
                            'numeric_price' => $salePrice,
                            'original_price' => '₹'.number_format((float) $p->numeric_price),
                            'discount_percent' => $item->discountBadgeLabel($p->numeric_price),
                            'savings' => $item->savingsLabel($p->numeric_price),
                            'stock_label' => $p->stock_quantity <= 5 ? "Only {$p->stock_quantity} left" : 'Limited stock',
                            'rating' => (float) $p->rating,
                            'reviews' => (int) $p->reviews,
                            'desc' => $p->desc,
                            'image' => $p->image,
                        ];
                    })->values()->all();
                }
            }
        } catch (\Throwable $e) {
            // Graceful fallback to static content if database is not reachable yet
        }

        return view('landing.index', [
            'content' => $content,
        ]);
    }
}
