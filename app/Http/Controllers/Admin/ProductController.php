<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products with search and category filtering.
     */
    public function index(Request $request): View
    {
        $query = Product::query()->with('categoryItem');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        if ($cat = $request->input('category')) {
            $query->where('category_slug', $cat);
        }

        if ($stock = $request->input('stock')) {
            if ($stock === 'in_stock') {
                $query->where('in_stock', true);
            } elseif ($stock === 'out_of_stock') {
                $query->where('in_stock', false);
            }
        }

        $products = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $cat,
            'stockFilter' => $stock,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', [
            'product' => new Product,
            'categories' => $categories,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'hsn_code' => ['nullable', 'string', 'max:20'],
            'gst_rate' => ['nullable', 'numeric', 'min:0', 'max:28'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'material' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:50'],
            'numeric_price' => ['required', 'integer', 'min:0'],
            'original_price' => ['nullable', 'string', 'max:50'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:1'],
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'badge' => ['nullable', 'string', 'max:50'],
            'desc' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'highlights' => ['nullable', 'array'],
            'highlights.*' => ['nullable', 'string', 'max:255'],
            'fabric_summary' => ['nullable', 'string', 'max:2000'],
            'weave_density' => ['nullable', 'string', 'max:255'],
            'craft_origin' => ['nullable', 'string', 'max:255'],
            'wash_care' => ['nullable', 'string', 'max:3000'],
            'shipping_returns' => ['nullable', 'string', 'max:3000'],
            'in_stock' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category = null;
        if (! empty($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
        }

        // Handle primary & gallery image uploads
        $imagePath = $validated['image'] ?? '/images/products/bedsheet-set.webp';
        if ($request->hasFile('image_file')) {
            $imagePath = $this->storeUploadedImage($request->file('image_file'), $validated['name']);
        }

        $gallery = [$imagePath];
        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $file) {
                $uploaded = $this->storeUploadedImage($file, $validated['name']);
                if (! in_array($uploaded, $gallery, true)) {
                    $gallery[] = $uploaded;
                }
            }
        }

        $price = ! empty($validated['price']) ? $validated['price'] : '₹'.number_format($validated['numeric_price']);
        $stockQty = $validated['stock_quantity'] ?? 50;

        $product = Product::create([
            'category_id' => $category?->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sku' => $validated['sku'] ?? ('MNH-'.strtoupper(Str::random(6))),
            'hsn_code' => $validated['hsn_code'] ?? '6304',
            'gst_rate' => $validated['gst_rate'] ?? 5.00,
            'stock_quantity' => $stockQty,
            'opening_stock' => $stockQty,
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? 10,
            'category' => $category?->name ?? 'General',
            'category_slug' => $category?->slug ?? 'general',
            'category_slugs' => $category ? [$category->slug, $category->parent_slug] : ['general'],
            'material' => $validated['material'] ?? null,
            'price' => $price,
            'numeric_price' => $validated['numeric_price'],
            'original_price' => $validated['original_price'] ?? null,
            'rating' => $validated['rating'] ?? 5.0,
            'reviews' => $validated['reviews'] ?? 0,
            'badge' => $validated['badge'] ?? null,
            'desc' => $validated['desc'] ?? null,
            'image' => $imagePath,
            'gallery' => $gallery,
            'highlights' => $this->sanitizeHighlights($validated['highlights'] ?? []),
            'fabric_details' => $this->buildFabricDetails($validated),
            'wash_care' => $this->nullableTrim($validated['wash_care'] ?? null),
            'shipping_returns' => $this->nullableTrim($validated['shipping_returns'] ?? null),
            'in_stock' => $request->boolean('in_stock', true) && $stockQty > 0,
            'is_featured' => $request->boolean('is_featured'),
            'is_bestseller' => $request->boolean('is_bestseller'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'initial',
            'previous_quantity' => 0,
            'quantity_changed' => $stockQty,
            'updated_quantity' => $stockQty,
            'user_id' => auth()->id(),
            'reason' => 'Product initial inventory',
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $categories = Category::orderBy('name')->get();

        return view('admin.products.form', [
            'product' => $product,
            'categories' => $categories,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'hsn_code' => ['nullable', 'string', 'max:20'],
            'gst_rate' => ['nullable', 'numeric', 'min:0', 'max:28'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'material' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:50'],
            'numeric_price' => ['required', 'integer', 'min:0'],
            'original_price' => ['nullable', 'string', 'max:50'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:1'],
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'badge' => ['nullable', 'string', 'max:50'],
            'desc' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'gallery_files' => ['nullable', 'array'],
            'gallery_files.*' => ['image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'replace_gallery_files' => ['nullable', 'array'],
            'replace_gallery_files.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'],
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['string'],
            'gallery_order' => ['nullable', 'array'],
            'primary_image' => ['nullable', 'string', 'max:500'],
            'highlights' => ['nullable', 'array'],
            'highlights.*' => ['nullable', 'string', 'max:255'],
            'fabric_summary' => ['nullable', 'string', 'max:2000'],
            'weave_density' => ['nullable', 'string', 'max:255'],
            'craft_origin' => ['nullable', 'string', 'max:255'],
            'wash_care' => ['nullable', 'string', 'max:3000'],
            'shipping_returns' => ['nullable', 'string', 'max:3000'],
            'in_stock' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category = null;
        if (! empty($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
        }

        // Rebuild ordered gallery preserving existing images unless removed/replaced
        $existingGallery = $product->allImages();
        $removeSet = array_flip($validated['remove_gallery'] ?? []);
        $orderMap = $validated['gallery_order'] ?? [];
        $replaceFiles = $request->file('replace_gallery_files', []);

        $galleryItems = [];
        foreach ($existingGallery as $idx => $path) {
            if (isset($removeSet[$path])) {
                continue;
            }
            $finalPath = $path;
            if (isset($replaceFiles[$idx]) && $replaceFiles[$idx]) {
                $finalPath = $this->storeUploadedImage($replaceFiles[$idx], $validated['name']);
            }
            $galleryItems[] = [
                'path' => $finalPath,
                'was_primary' => ($validated['primary_image'] ?? '') === $path,
                'order' => isset($orderMap[$path]) ? (int) $orderMap[$path] : $idx,
            ];
        }

        usort($galleryItems, static fn (array $a, array $b): int => $a['order'] <=> $b['order']);
        $gallery = array_values(array_unique(array_column($galleryItems, 'path')));

        if ($request->hasFile('image_file')) {
            $uploadedPrimary = $this->storeUploadedImage($request->file('image_file'), $validated['name']);
            array_unshift($gallery, $uploadedPrimary);
            $gallery = array_values(array_unique($gallery));
            $imagePath = $uploadedPrimary;
        } else {
            $selectedPrimary = collect($galleryItems)->firstWhere('was_primary', true)['path'] ?? null;
            if ($selectedPrimary) {
                $imagePath = $selectedPrimary;
            } elseif (! empty($validated['image']) && $validated['image'] !== $product->image) {
                $imagePath = $validated['image'];
                if (! in_array($imagePath, $gallery, true)) {
                    array_unshift($gallery, $imagePath);
                }
            } else {
                $imagePath = $gallery[0] ?? $product->image;
            }
        }

        if ($request->hasFile('gallery_files')) {
            foreach ($request->file('gallery_files') as $file) {
                $uploaded = $this->storeUploadedImage($file, $validated['name']);
                if (! in_array($uploaded, $gallery, true)) {
                    $gallery[] = $uploaded;
                }
            }
        }

        if ($imagePath && ! in_array($imagePath, $gallery, true)) {
            array_unshift($gallery, $imagePath);
        }

        $price = ! empty($validated['price']) ? $validated['price'] : '₹'.number_format($validated['numeric_price']);
        $newStock = isset($validated['stock_quantity']) ? (int) $validated['stock_quantity'] : $product->stock_quantity;

        // Check if stock changed directly in edit form and log movement
        if ($newStock !== $product->stock_quantity) {
            $delta = $newStock - $product->stock_quantity;
            StockMovement::create([
                'product_id' => $product->id,
                'movement_type' => 'manual_adjustment',
                'previous_quantity' => $product->stock_quantity,
                'quantity_changed' => $delta,
                'updated_quantity' => $newStock,
                'user_id' => auth()->id(),
                'reason' => 'Product edit form manual adjustment',
            ]);
        }

        $product->update([
            'category_id' => $category?->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'sku' => $validated['sku'] ?? $product->sku,
            'hsn_code' => $validated['hsn_code'] ?? ($product->hsn_code ?: '6304'),
            'gst_rate' => $validated['gst_rate'] ?? ($product->gst_rate ?: 5.00),
            'stock_quantity' => $newStock,
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? ($product->low_stock_threshold ?: 10),
            'category' => $category?->name ?? $product->category,
            'category_slug' => $category?->slug ?? $product->category_slug,
            'category_slugs' => $category ? array_unique(array_filter([$category->slug, $category->parent_slug])) : $product->category_slugs,
            'material' => $validated['material'] ?? null,
            'price' => $price,
            'numeric_price' => $validated['numeric_price'],
            'original_price' => $validated['original_price'] ?? null,
            'rating' => $validated['rating'] ?? $product->rating,
            'reviews' => $validated['reviews'] ?? $product->reviews,
            'badge' => $validated['badge'] ?? null,
            'desc' => $validated['desc'] ?? null,
            'image' => $imagePath,
            'gallery' => array_values($gallery),
            'highlights' => $this->sanitizeHighlights($validated['highlights'] ?? []),
            'fabric_details' => $this->buildFabricDetails($validated),
            'wash_care' => $this->nullableTrim($validated['wash_care'] ?? null),
            'shipping_returns' => $this->nullableTrim($validated['shipping_returns'] ?? null),
            'in_stock' => $request->boolean('in_stock') && $newStock > 0,
            'is_featured' => $request->boolean('is_featured'),
            'is_bestseller' => $request->boolean('is_bestseller'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' updated successfully.");
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$name}' deleted successfully.");
    }

    /**
     * Safely store an uploaded product image in public/images/uploads.
     */
    protected function storeUploadedImage(UploadedFile $file, string $productName): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: 'webp');
        $filename = time().'_'.Str::random(6).'_'.Str::slug($productName).'.'.$ext;
        $file->move(public_path('images/uploads'), $filename);

        return '/images/uploads/'.$filename;
    }

    /**
     * Clean and re-index product highlights array.
     *
     * @param  array<int, string|null>  $highlights
     * @return array<int, string>|null
     */
    protected function sanitizeHighlights(array $highlights): ?array
    {
        $clean = array_values(array_filter(array_map(
            static fn ($item): string => trim((string) $item),
            $highlights
        ), static fn (string $item): bool => $item !== ''));

        return $clean !== [] ? $clean : null;
    }

    /**
     * Build structured fabric_details payload or null when unconfigured.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, string>|null
     */
    protected function buildFabricDetails(array $validated): ?array
    {
        $details = array_filter([
            'summary' => $this->nullableTrim($validated['fabric_summary'] ?? null),
            'weave_density' => $this->nullableTrim($validated['weave_density'] ?? null),
            'craft_origin' => $this->nullableTrim($validated['craft_origin'] ?? null),
        ], static fn ($v): bool => $v !== null);

        return $details !== [] ? $details : null;
    }

    protected function nullableTrim(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
