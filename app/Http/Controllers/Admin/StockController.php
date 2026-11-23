<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Stock Management Dashboard & Product Stock List.
     */
    public function index(Request $request): View
    {
        $query = Product::query()->with(['categoryItem']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('hsn_code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('stock_status')) {
            if ($status === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($status === 'low_stock') {
                $query->where('stock_quantity', '>', 0)
                    ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            } elseif ($status === 'in_stock') {
                $query->whereColumn('stock_quantity', '>', 'low_stock_threshold');
            }
        }

        $products = $query->orderBy('stock_quantity', 'asc')->paginate(15)->withQueryString();

        // Stock Dashboard Statistics
        $totalProducts = Product::count();
        $totalAvailableStock = (int) Product::sum('stock_quantity');
        $totalSoldQuantity = (int) Product::sum('sold_quantity');
        $lowStockProducts = Product::where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->count();
        $outOfStockProducts = Product::where('stock_quantity', '<=', 0)->count();

        $recentlyAddedStock = StockMovement::whereIn('movement_type', ['initial', 'restock'])
            ->latest()
            ->take(5)
            ->with(['product', 'user'])
            ->get();

        return view('admin.stock.index', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'totalAvailableStock' => $totalAvailableStock,
            'totalSoldQuantity' => $totalSoldQuantity,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'recentlyAddedStock' => $recentlyAddedStock,
            'search' => $search,
            'statusFilter' => $status,
        ]);
    }

    /**
     * Adjust stock for a product (add, reduce, record damaged, manual adjustment).
     */
    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'movement_type' => ['required', 'in:restock,damaged,manual_adjustment,returned'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = (int) $validated['quantity'];
        $movementType = $validated['movement_type'];

        // Determine sign based on action
        if (in_array($movementType, ['damaged'], true)) {
            $delta = -$qty;
        } elseif ($movementType === 'manual_adjustment') {
            $direction = $request->input('adjustment_direction', 'add');
            $delta = ($direction === 'reduce') ? -$qty : $qty;
        } else {
            // restock or returned
            $delta = $qty;
        }

        $userId = auth()->id();
        $this->stockService->adjustStock($product, $delta, $movementType, $userId, $validated['reason'] ?? null);

        return back()->with('success', "Stock updated for '{$product->name}'. Quantity change: ".($delta > 0 ? "+{$delta}" : "{$delta}"));
    }

    /**
     * Stock Movements Audit Trail.
     */
    public function movements(Request $request): View
    {
        $query = StockMovement::query()->with(['product', 'user', 'order']);

        if ($type = $request->input('movement_type')) {
            $query->where('movement_type', $type);
        }

        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $movements = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.stock.movements', [
            'movements' => $movements,
            'products' => $products,
            'typeFilter' => $type,
            'productFilter' => $productId,
            'search' => $search,
        ]);
    }

    /**
     * Stock Analytics & Performance View.
     */
    public function analysis(Request $request): View
    {
        $bestSellers = Product::orderBy('sold_quantity', 'desc')->take(10)->get();
        $slowMoving = Product::where('stock_quantity', '>', 0)
            ->orderBy('sold_quantity', 'asc')
            ->take(10)
            ->get();

        $totalUnitsSold = (int) Product::sum('sold_quantity');
        $totalCurrentStock = (int) Product::sum('stock_quantity');
        $totalStockAdded = (int) StockMovement::whereIn('movement_type', ['initial', 'restock'])->sum('quantity_changed');

        return view('admin.stock.analysis', [
            'bestSellers' => $bestSellers,
            'slowMoving' => $slowMoving,
            'totalUnitsSold' => $totalUnitsSold,
            'totalCurrentStock' => $totalCurrentStock,
            'totalStockAdded' => $totalStockAdded,
        ]);
    }
}
