<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with initial admin, categories, and products.
     */
    public function run(): void
    {
        // 1. Seed or update Default Administrator
        $adminEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@menahem.com');
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD', 'admin12345');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Menahem Administrator',
                'password' => Hash::make($adminPassword),
                'role' => UserRole::Admin,
            ]
        );

        // Also seed an Editor for role testing
        User::updateOrCreate(
            ['email' => 'editor@menahem.com'],
            [
                'name' => 'Menahem Catalog Editor',
                'password' => Hash::make('editor12345'),
                'role' => UserRole::Editor,
            ]
        );

        // 2. Seed Categories recursively from CategoryService
        $hierarchy = CategoryService::getStaticHierarchy();
        $this->seedCategoriesRecursive($hierarchy, null, null, '');

        // 3. Seed Products from content.php
        $contentPath = resource_path('content.php');
        if (file_exists($contentPath)) {
            $content = require $contentPath;
            $products = $content['CATALOG']['products'] ?? [];

            foreach ($products as $index => $item) {
                $categorySlug = $item['category_slug'] ?? 'general';
                $matchedCategory = Category::where('slug', $categorySlug)->first();

                // Generate slug if not present
                $slug = Str::slug($item['name'] ?? 'product-'.$index);

                Product::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'category_id' => $matchedCategory?->id,
                        'name' => $item['name'] ?? 'Product',
                        'slug' => $slug,
                        'category' => $item['category'] ?? ($matchedCategory?->name ?? 'General'),
                        'category_slug' => $categorySlug,
                        'category_slugs' => $item['category_slugs'] ?? [$categorySlug],
                        'material' => $item['material'] ?? null,
                        'price' => $item['price'] ?? '₹'.($item['numeric_price'] ?? 0),
                        'numeric_price' => (int) ($item['numeric_price'] ?? 0),
                        'original_price' => $item['original_price'] ?? null,
                        'rating' => (float) ($item['rating'] ?? 5.0),
                        'reviews' => (int) ($item['reviews'] ?? 0),
                        'badge' => $item['badge'] ?? null,
                        'desc' => $item['desc'] ?? null,
                        'image' => $item['image'] ?? '/images/products/bedsheet-set.webp',
                        'gallery' => $item['gallery'] ?? [$item['image'] ?? '/images/products/bedsheet-set.webp'],
                        'in_stock' => true,
                        'is_featured' => ($index < 4),
                        'is_bestseller' => ($item['badge'] ?? '') === 'Bestseller',
                        'sort_order' => $index,
                    ]
                );
            }
        }

        // 4. Seed Initial Customer Inquiries / Leads
        if (Inquiry::count() === 0) {
            Inquiry::create([
                'name' => 'Priya Sharma',
                'email' => 'priya.sharma@example.com',
                'phone' => '+91 98201 23456',
                'type' => 'curtain_measurement',
                'product_name' => 'Premium Blackout Window Curtains (Pair)',
                'message' => 'Need customized floor-to-ceiling pleated curtains for 3 living room French windows.',
                'details' => [
                    'window_width' => '84 inches',
                    'window_height' => '108 inches',
                    'fabric' => 'Belgian Linen Slate Gray',
                    'lining' => '100% Blackout',
                ],
                'status' => 'new',
            ]);

            Inquiry::create([
                'name' => 'Rajesh Verma',
                'email' => 'rajesh.v@example.com',
                'phone' => '+91 97110 56789',
                'type' => 'general',
                'product_name' => 'Cotton Bedsheet & Dohar Set',
                'message' => 'Interested in bulk orders (12 sets) for a boutique heritage resort in Udaipur.',
                'details' => [
                    'quantity' => 12,
                    'destination' => 'Udaipur, Rajasthan',
                ],
                'status' => 'contacted',
                'admin_notes' => 'Spoke over WhatsApp. Shared corporate quotation catalog.',
            ]);
        }
    }

    /**
     * Recursive helper to seed hierarchical categories.
     *
     * @param  array<string, mixed>  $nodes
     */
    private function seedCategoriesRecursive(array $nodes, ?int $parentId, ?string $parentSlug, string $parentPath): void
    {
        $sort = 0;
        foreach ($nodes as $key => $node) {
            $slug = $node['slug'] ?? $key;
            $currentPath = $parentPath ? "{$parentPath}/{$slug}" : $slug;

            $category = Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $node['name'] ?? ucfirst(str_replace('-', ' ', $slug)),
                    'parent_id' => $parentId,
                    'parent_slug' => $parentSlug,
                    'path' => $currentPath,
                    'image' => $node['image'] ?? null,
                    'description' => $node['description'] ?? null,
                    'badge' => $node['badge'] ?? null,
                    'sort_order' => $sort++,
                ]
            );

            if (! empty($node['subcategories']) && is_array($node['subcategories'])) {
                $this->seedCategoriesRecursive($node['subcategories'], $category->id, $slug, $currentPath);
            }
        }
    }
}
