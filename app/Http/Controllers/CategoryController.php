<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display the main Shop by Category overview page.
     * Features all 6 primary categories in a clean mobile-first layout.
     */
    public function index(): View
    {
        $content = require resource_path('content.php');
        $categories = CategoryService::getHierarchy();

        $breadcrumbs = [
            [
                'name' => 'Home',
                'url' => route('home'),
                'is_current' => false,
            ],
            [
                'name' => 'Shop by Category',
                'url' => null,
                'is_current' => true,
            ],
        ];

        return view('categories.index', [
            'content' => $content,
            'categories' => $categories,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Display a specific category level or redirect to products if at leaf level.
     */
    public function show(string $path): View|RedirectResponse
    {
        $content = require resource_path('content.php');
        $resolved = CategoryService::resolveByPath($path);

        if (! $resolved) {
            $resolved = CategoryService::findBySlug($path);
        }

        if (! $resolved) {
            abort(404, 'Category not found');
        }

        $node = $resolved['node'];

        // If this category has no subcategories (it is a leaf node),
        // redirect straight to the product listing filtered by this leaf.
        if (CategoryService::isLeaf($node)) {
            return redirect()->route('products.index', [
                'category' => $node['slug'],
            ]);
        }

        $breadcrumbs = CategoryService::getBreadcrumbs($resolved);

        // Compute Parent Back link
        if (! empty($resolved['parent']) && ! empty($resolved['parent_path'])) {
            $parentName = $resolved['parent']['name'];
            $parentUrl = url('/categories/'.$resolved['parent_path']);
        } else {
            $parentName = 'All Categories';
            $parentUrl = route('categories.index');
        }

        return view('categories.show', [
            'content' => $content,
            'category' => $node,
            'path' => $resolved['path'],
            'subcategories' => $node['subcategories'] ?? [],
            'breadcrumbs' => $breadcrumbs,
            'parentName' => $parentName,
            'parentUrl' => $parentUrl,
        ]);
    }
}
