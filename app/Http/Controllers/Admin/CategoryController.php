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
     * Display a listing of categories (Tree View or Main Categories View or Subcategory Drilldown).
     */
    public function index(Request $request): View
    {
        $viewMode = $request->input('view', 'tree'); // 'tree', 'main', or drilldown via 'parent_id'
        $parentId = $request->input('parent_id');
        $search = $request->input('search');

        $activeParent = null;
        if ($parentId) {
            $activeParent = Category::with('parent')->findOrFail($parentId);
            $subcategories = Category::where('parent_id', $parentId)
                ->withCount(['subcategories', 'products'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return view('admin.categories.index', [
                'viewMode' => 'subcategories',
                'activeParent' => $activeParent,
                'subcategories' => $subcategories,
                'search' => $search,
            ]);
        }

        if ($viewMode === 'main') {
            $mainCategoriesQuery = Category::whereNull('parent_id')
                ->withCount(['subcategories', 'products'])
                ->orderBy('sort_order')
                ->orderBy('name');

            if ($search) {
                $mainCategoriesQuery->where('name', 'like', "%{$search}%");
            }

            $mainCategories = $mainCategoriesQuery->paginate(20)->withQueryString();

            return view('admin.categories.index', [
                'viewMode' => 'main',
                'mainCategories' => $mainCategories,
                'search' => $search,
            ]);
        }

        // Default: Full Interactive Hierarchical Tree View
        $treeQuery = Category::whereNull('parent_id')
            ->with(['allChildren' => function ($q) {
                $q->withCount(['subcategories', 'products'])->orderBy('sort_order')->orderBy('name');
            }])
            ->withCount(['subcategories', 'products'])
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($search) {
            $treeQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('subcategories', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $rootCategories = $treeQuery->get();

        return view('admin.categories.index', [
            'viewMode' => 'tree',
            'rootCategories' => $rootCategories,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new category or subcategory.
     */
    public function create(Request $request): View
    {
        $category = new Category([
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $parent = null;
        if ($request->has('parent_id')) {
            $parent = Category::find($request->input('parent_id'));
            if ($parent) {
                $category->parent_id = $parent->id;
            }
        }

        $allCategories = $this->getIndentedCategoryOptions();

        return view('admin.categories.form', [
            'category' => $category,
            'parent' => $parent,
            'allCategories' => $allCategories,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created category or subcategory.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'image_file' => ['nullable', 'image', 'max:5120'], // 5MB image upload
            'image_url' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'badge' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $parent = ! empty($validated['parent_id']) ? Category::find($validated['parent_id']) : null;

        // Auto-generate unique slug
        $baseSlug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $path = $parent ? "{$parent->path}/{$slug}" : $slug;

        // Image upload handling
        $imagePath = $validated['image_url'] ?? null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images/uploads'), $filename);
            $imagePath = '/images/uploads/'.$filename;
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $parent?->id,
            'parent_slug' => $parent?->slug,
            'path' => $path,
            'image' => $imagePath,
            'description' => $validated['description'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $redirectRoute = $parent
            ? route('admin.categories.index', ['parent_id' => $parent->id])
            : route('admin.categories.index', ['view' => 'main']);

        return redirect($redirectRoute)
            ->with('success', "Category '{$category->name}' created successfully.");
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $allCategories = $this->getIndentedCategoryOptions($category->id);

        return view('admin.categories.form', [
            'category' => $category,
            'parent' => $category->parent,
            'allCategories' => $allCategories,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id', 'not_in:'.$category->id],
            'image_file' => ['nullable', 'image', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'remove_image' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
            'badge' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        // Prevent circular parenting (cannot set child or descendant as parent)
        if (! empty($validated['parent_id'])) {
            $descendantIds = $this->getAllDescendantIds($category);
            if (in_array((int) $validated['parent_id'], $descendantIds, true)) {
                return back()->withInput()->withErrors([
                    'parent_id' => 'Cannot set a child subcategory as parent. This would cause an infinite loop.',
                ]);
            }
        }

        $parent = ! empty($validated['parent_id']) ? Category::find($validated['parent_id']) : null;

        // Auto-generate or format slug
        $baseSlug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        // Image upload handling
        $imagePath = $category->image;
        if ($request->boolean('remove_image')) {
            $imagePath = null;
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('images/uploads'), $filename);
            $imagePath = '/images/uploads/'.$filename;
        } elseif (! empty($validated['image_url'])) {
            $imagePath = $validated['image_url'];
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $parent?->id,
            'parent_slug' => $parent?->slug,
            'image' => $imagePath,
            'description' => $validated['description'] ?? null,
            'badge' => $validated['badge'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        // Rebuild paths for category and all descendants
        $category->rebuildNestedPaths();

        return redirect()->route('admin.categories.index', $category->parent_id ? ['parent_id' => $category->parent_id] : ['view' => 'main'])
            ->with('success', "Category '{$category->name}' updated successfully.");
    }

    /**
     * Toggle Active / Inactive status of a category.
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->is_active = ! $category->is_active;
        $category->save();

        $statusStr = $category->is_active ? 'Activated' : 'Deactivated';

        return back()->with('success', "Category '{$category->name}' {$statusStr}.");
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;

        // Prevent deletion if category has subcategories
        if ($category->subcategories()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$name}' because it contains subcategories. Please delete or reassign them first.");
        }

        // Prevent deletion if category has associated products
        if ($category->products()->count() > 0) {
            return back()->with('error', "Cannot delete category '{$name}' because it has {$category->products()->count()} associated products.");
        }

        $parentId = $category->parent_id;
        $category->delete();

        $redirectRoute = $parentId
            ? route('admin.categories.index', ['parent_id' => $parentId])
            : route('admin.categories.index', ['view' => 'main']);

        return redirect($redirectRoute)->with('success', "Category '{$name}' deleted successfully.");
    }

    /**
     * Generate indented category list for parent selector dropdown.
     */
    protected function getIndentedCategoryOptions(?int $excludeId = null): array
    {
        $options = [];
        $roots = Category::whereNull('parent_id')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($roots as $root) {
            $options[] = [
                'id' => $root->id,
                'name' => $root->name,
                'depth' => 0,
            ];
            $this->appendDescendantOptions($root, $options, 1, $excludeId);
        }

        return $options;
    }

    /**
     * Recursively append children options with indentation level.
     */
    protected function appendDescendantOptions(Category $parent, array &$options, int $depth, ?int $excludeId = null): void
    {
        $children = Category::where('parent_id', $parent->id)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($children as $child) {
            $options[] = [
                'id' => $child->id,
                'name' => $child->name,
                'depth' => $depth,
            ];
            $this->appendDescendantOptions($child, $options, $depth + 1, $excludeId);
        }
    }

    /**
     * Get all descendant IDs of a category recursively.
     */
    protected function getAllDescendantIds(Category $category): array
    {
        $ids = [];
        foreach ($category->subcategories as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllDescendantIds($child));
        }

        return $ids;
    }
}
