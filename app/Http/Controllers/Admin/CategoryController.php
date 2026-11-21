<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): View
    {
        $categories = Category::with('parent')
            ->withCount('products')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->paginate(25);

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.form', [
            'category' => new Category,
            'parents' => $parents,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'badge' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $parent = null;
        if (! empty($validated['parent_id'])) {
            $parent = Category::find($validated['parent_id']);
        }

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $path = $parent ? "{$parent->path}/{$slug}" : $slug;

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $parent?->id,
            'parent_slug' => $parent?->slug,
            'path' => $path,
            'image' => $validated['image'] ?? null,
            'description' => $validated['description'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$validated['name']}' created successfully.");
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.form', [
            'category' => $category,
            'parents' => $parents,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,'.$category->id],
            'parent_id' => ['nullable', 'exists:categories,id', 'different:'.$category->id],
            'image' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'badge' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $parent = null;
        if (! empty($validated['parent_id'])) {
            $parent = Category::find($validated['parent_id']);
        }

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $path = $parent ? "{$parent->path}/{$slug}" : $slug;

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $parent?->id,
            'parent_slug' => $parent?->slug,
            'path' => $path,
            'image' => $validated['image'] ?? $category->image,
            'description' => $validated['description'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' updated successfully.");
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;

        // Check if category has child categories or products
        if ($category->subcategories()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$name}' because it contains subcategories. Please reassign or delete them first.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$name}' deleted successfully.");
    }
}
