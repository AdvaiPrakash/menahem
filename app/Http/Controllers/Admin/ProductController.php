<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'material' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:50'],
            'numeric_price' => ['required', 'integer', 'min:0'],
            'original_price' => ['nullable', 'string', 'max:50'],
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'badge' => ['nullable', 'string', 'max:50'],
            'desc' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'max:5120'], // 5MB max
            'in_stock' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category = null;
        if (! empty($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
        }

        // Handle image upload if provided
        $imagePath = $validated['image'] ?? '/images/products/bedsheet-set.webp';
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time().'_'.Str::slug($validated['name']).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images/uploads'), $filename);
            $imagePath = '/images/uploads/'.$filename;
        }

        $price = ! empty($validated['price']) ? $validated['price'] : '₹'.number_format($validated['numeric_price']);

        Product::create([
            'category_id' => $category?->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
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
            'gallery' => [$imagePath],
            'in_stock' => $request->boolean('in_stock', true),
            'is_featured' => $request->boolean('is_featured'),
            'is_bestseller' => $request->boolean('is_bestseller'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'material' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:50'],
            'numeric_price' => ['required', 'integer', 'min:0'],
            'original_price' => ['nullable', 'string', 'max:50'],
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'reviews' => ['nullable', 'integer', 'min:0'],
            'badge' => ['nullable', 'string', 'max:50'],
            'desc' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'in_stock' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_bestseller' => ['boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $category = null;
        if (! empty($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
        }

        $imagePath = $product->image;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time().'_'.Str::slug($validated['name']).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images/uploads'), $filename);
            $imagePath = '/images/uploads/'.$filename;
        } elseif (! empty($validated['image'])) {
            $imagePath = $validated['image'];
        }

        $price = ! empty($validated['price']) ? $validated['price'] : '₹'.number_format($validated['numeric_price']);

        $product->update([
            'category_id' => $category?->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
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
            'in_stock' => $request->boolean('in_stock'),
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
        $name = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$name}' deleted successfully.");
    }
}
