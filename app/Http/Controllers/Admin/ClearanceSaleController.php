<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClearanceItem;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClearanceSaleController extends Controller
{
    /**
     * Display the dedicated Clearance Sale Management workspace.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $clearanceItems = ClearanceItem::ensureTableExists()
            ? ClearanceItem::with('product')
                ->orderBy('display_order')
                ->orderBy('id')
                ->get()
            : collect();

        $selectedIds = $clearanceItems->pluck('product_id')->all();

        $availableQuery = Product::query()
            ->whereNotIn('id', $selectedIds);

        if ($search !== '') {
            $availableQuery->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $availableProducts = $availableQuery
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.clearance.index', [
            'clearanceItems' => $clearanceItems,
            'availableProducts' => $availableProducts,
            'search' => $search,
        ]);
    }

    /**
     * Add one or multiple products to the Clearance Sale with validated discount settings.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->canEditContent(), 403);
        ClearanceItem::ensureTableExists();

        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', 'integer', 'exists:products,id'],
            'discount_type' => ['required', 'string', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'show_in_all_products' => ['nullable', 'boolean'],
        ]);

        $discountType = $validated['discount_type'];
        $discountValue = (float) $validated['discount_value'];

        if ($discountType === 'percentage' && $discountValue >= 100) {
            return back()->withInput()->withErrors([
                'discount_value' => 'Percentage discount must be less than 100%.',
            ]);
        }

        $products = Product::whereIn('id', $validated['product_ids'])->get();
        foreach ($products as $product) {
            if ($discountType === 'fixed' && $discountValue > (float) $product->numeric_price) {
                return back()->withInput()->withErrors([
                    'discount_value' => "Fixed discount (₹{$discountValue}) cannot exceed '{$product->name}' price (₹{$product->numeric_price}).",
                ]);
            }
        }

        $nextOrder = ((int) ClearanceItem::max('display_order')) + 1;
        $showOnHomepage = $request->boolean('show_on_homepage');
        $showInAllProducts = $request->boolean('show_in_all_products');

        foreach ($products as $product) {
            ClearanceItem::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'show_on_homepage' => $showOnHomepage,
                    'show_in_all_products' => $showInAllProducts,
                    'display_order' => $nextOrder++,
                    'is_active' => true,
                ]
            );
        }

        Setting::set('clearance_sale_managed', '1');

        return redirect()
            ->route('admin.clearance.index')
            ->with('success', count($products).' product(s) added to Clearance Sale.');
    }

    /**
     * Update discount, display order, and independent visibility settings for a clearance item.
     */
    public function update(Request $request, ClearanceItem $clearanceItem): RedirectResponse
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $validated = $request->validate([
            'discount_type' => ['required', 'string', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'display_order' => ['required', 'integer', 'min:0'],
            'show_on_homepage' => ['nullable', 'boolean'],
            'show_in_all_products' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product = $clearanceItem->product;
        $discountType = $validated['discount_type'];
        $discountValue = (float) $validated['discount_value'];

        if ($discountType === 'percentage' && $discountValue >= 100) {
            return back()->withErrors([
                'discount_value' => 'Percentage discount must be less than 100%.',
            ]);
        }

        if ($discountType === 'fixed' && $product && $discountValue > (float) $product->numeric_price) {
            return back()->withErrors([
                'discount_value' => "Fixed discount cannot exceed '{$product->name}' original price (₹{$product->numeric_price}).",
            ]);
        }

        $clearanceItem->update([
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'display_order' => (int) $validated['display_order'],
            'show_on_homepage' => $request->boolean('show_on_homepage'),
            'show_in_all_products' => $request->boolean('show_in_all_products'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        Setting::set('clearance_sale_managed', '1');

        return redirect()
            ->route('admin.clearance.index')
            ->with('success', 'Clearance sale settings updated.');
    }

    /**
     * Remove a product from Clearance Sale without deleting the catalog product.
     */
    public function destroy(ClearanceItem $clearanceItem): RedirectResponse
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $clearanceItem->delete();
        Setting::set('clearance_sale_managed', '1');

        return redirect()
            ->route('admin.clearance.index')
            ->with('success', 'Product removed from Clearance Sale.');
    }
}
