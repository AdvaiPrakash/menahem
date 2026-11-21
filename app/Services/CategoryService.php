<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    /**
     * Get the complete category hierarchy matching the client specification.
     * Pulls dynamically from MySQL database with static matrix fallback.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getHierarchy(): array
    {
        try {
            if (class_exists(Category::class)) {
                $roots = Category::whereNull('parent_id')
                    ->with('subcategories.subcategories')
                    ->orderBy('sort_order')
                    ->get();

                if ($roots->isNotEmpty()) {
                    return self::buildTreeFromCategories($roots);
                }
            }
        } catch (\Throwable $e) {
            // Keep static fallback
        }

        return self::getStaticHierarchy();
    }

    /**
     * Build nested array structure from Category models.
     */
    private static function buildTreeFromCategories($collection): array
    {
        $tree = [];
        foreach ($collection as $cat) {
            $sub = [];
            if ($cat->relationLoaded('subcategories') && $cat->subcategories->isNotEmpty()) {
                $sub = self::buildTreeFromCategories($cat->subcategories);
            }
            $tree[$cat->slug] = [
                'name' => $cat->name,
                'slug' => $cat->slug,
                'image' => $cat->image ?: '/images/categories/bedsheets.webp',
                'description' => $cat->description ?: '',
                'badge' => $cat->badge,
                'subcategories' => $sub,
            ];
        }

        return $tree;
    }

    /**
     * Static category hierarchy fallback.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getStaticHierarchy(): array
    {
        return [
            'bedsheets-dohars' => [
                'name' => 'Bedsheets & Dohars',
                'slug' => 'bedsheets-dohars',
                'image' => '/images/categories/bedsheets.webp',
                'description' => '400TC mulmul sheets, fitted covers, embroidered sets & summer dohars',
                'subcategories' => [
                    'cotton-bedsheets' => [
                        'name' => 'Cotton Bedsheets',
                        'slug' => 'cotton-bedsheets',
                        'image' => '/images/products/bedsheet-set.webp',
                        'description' => 'Breathable 100% mulmul cotton bedsheets with matching pillow covers',
                        'subcategories' => [
                            'king-size-cotton-bedsheets' => [
                                'name' => 'King Size',
                                'slug' => 'king-size-cotton-bedsheets',
                                'image' => '/images/products/bedsheet-set.webp',
                                'description' => '108x108 inch generous king size sheets with pillow covers',
                                'subcategories' => [],
                            ],
                            'queen-size-cotton-bedsheets' => [
                                'name' => 'Queen Size',
                                'slug' => 'queen-size-cotton-bedsheets',
                                'image' => '/images/categories/bedsheets.webp',
                                'description' => '90x100 inch comfortable queen size sheets with pillow covers',
                                'subcategories' => [],
                            ],
                            'single-cotton-bedsheets' => [
                                'name' => 'Single',
                                'slug' => 'single-cotton-bedsheets',
                                'image' => '/images/products/bedsheet-set.webp',
                                'description' => '60x90 inch breathable sheets for single beds and daybeds',
                                'subcategories' => [],
                            ],
                        ],
                    ],
                    'fitted-bedsheets' => [
                        'name' => 'Fitted Bedsheets',
                        'slug' => 'fitted-bedsheets',
                        'image' => '/images/categories/bed-protectors.webp',
                        'description' => 'All-around 360° elastic hem for snug, wrinkle-free mattress fit',
                        'subcategories' => [
                            'king-size-fitted-bedsheets' => [
                                'name' => 'King Size',
                                'slug' => 'king-size-fitted-bedsheets',
                                'image' => '/images/categories/bed-protectors.webp',
                                'description' => 'Deep pocket fitted sheet fitting mattresses up to 12 inches',
                                'subcategories' => [],
                            ],
                            'queen-size-fitted-bedsheets' => [
                                'name' => 'Queen Size',
                                'slug' => 'queen-size-fitted-bedsheets',
                                'image' => '/images/products/bed-protector.webp',
                                'description' => 'Snug elastic grip for queen-size orthopedic mattresses',
                                'subcategories' => [],
                            ],
                        ],
                    ],
                    'embroidered-bedsheets' => [
                        'name' => 'Embroidered Bedsheets',
                        'slug' => 'embroidered-bedsheets',
                        'image' => '/images/categories/bedsheets.webp',
                        'description' => 'Delicate hand-guided needlework on crisp percale cotton',
                        'subcategories' => [
                            'king-size-embroidered-bedsheets' => [
                                'name' => 'King Size',
                                'slug' => 'king-size-embroidered-bedsheets',
                                'image' => '/images/categories/bedsheets.webp',
                                'description' => 'Intricate border embroidery on luxury king sheets with shams',
                                'subcategories' => [],
                            ],
                            'queen-size-embroidered-bedsheets' => [
                                'name' => 'Queen Size',
                                'slug' => 'queen-size-embroidered-bedsheets',
                                'image' => '/images/products/bedsheet-set.webp',
                                'description' => 'Artisanal tonal stitch work on serene queen sheets',
                                'subcategories' => [],
                            ],
                        ],
                    ],
                    'dohars' => [
                        'name' => 'Dohars',
                        'slug' => 'dohars',
                        'image' => '/images/products/dohar-quilt.webp',
                        'description' => 'Featherweight 3-layer summer mulmul quilts with microfiber core',
                        'subcategories' => [
                            'single-dohars' => [
                                'name' => 'Single',
                                'slug' => 'single-dohars',
                                'image' => '/images/products/dohar-quilt.webp',
                                'description' => '60x90 inch cloud-soft summer wrap for individual comfort',
                                'subcategories' => [],
                            ],
                            'double-dohars' => [
                                'name' => 'Double',
                                'slug' => 'double-dohars',
                                'image' => '/images/categories/dohars-quilts.webp',
                                'description' => '90x100 inch reversible double dohar for shared comfort',
                                'subcategories' => [],
                            ],
                            'kids-dohars' => [
                                'name' => 'Kids',
                                'slug' => 'kids-dohars',
                                'image' => '/images/products/dohar-quilt.webp',
                                'description' => 'Playful soothing prints crafted from gentle organic mulmul',
                                'subcategories' => [],
                            ],
                        ],
                    ],
                    'comforters' => [
                        'name' => 'Comforters',
                        'slug' => 'comforters',
                        'image' => '/images/categories/dohars-quilts.webp',
                        'description' => 'Plush all-season microfiber comforters with baffle-box quilting',
                        'subcategories' => [],
                    ],
                ],
            ],

            'curtains-drapes' => [
                'name' => 'Curtains & Drapes',
                'slug' => 'curtains-drapes',
                'image' => '/images/categories/curtains.webp',
                'description' => 'Light-filtering sheers & premium blackout custom drapes',
                'subcategories' => [
                    'sheer-curtains' => [
                        'name' => 'Sheer Curtains',
                        'slug' => 'sheer-curtains',
                        'image' => '/images/products/curtains-sheer.webp',
                        'description' => 'Airy daylight-diffusing sheers in natural Belgian linen blends',
                        'subcategories' => [
                            'ready-made-sheer-curtains' => [
                                'name' => 'Ready-made Curtains',
                                'slug' => 'ready-made-sheer-curtains',
                                'image' => '/images/products/curtains-sheer.webp',
                                'description' => 'Standard window & door lengths with pre-fitted antique brass eyelets',
                                'subcategories' => [],
                            ],
                            'customization-sheer-curtains' => [
                                'name' => 'Customization',
                                'slug' => 'customization-sheer-curtains',
                                'image' => '/images/categories/curtains.webp',
                                'description' => 'Custom header styles, pleated drape lengths & bespoke sizing',
                                'subcategories' => [],
                            ],
                        ],
                    ],
                    'blackout-curtains' => [
                        'name' => 'Blackout Curtains',
                        'slug' => 'blackout-curtains',
                        'image' => '/images/products/curtains-blackout.webp',
                        'description' => 'Thermal insulating drapes for deep sleep and complete light control',
                        'subcategories' => [
                            'ready-made-blackout-curtains' => [
                                'name' => 'Ready-made Curtains',
                                'slug' => 'ready-made-blackout-curtains',
                                'image' => '/images/products/curtains-blackout.webp',
                                'description' => 'Pre-stitched blackout pairs with rust-proof metal grommets',
                                'subcategories' => [],
                            ],
                            'customization-blackout-curtains' => [
                                'name' => 'Customization',
                                'slug' => 'customization-blackout-curtains',
                                'image' => '/images/categories/curtains.webp',
                                'description' => 'Tailored architectural drop heights, pinch pleats & blackout lining',
                                'subcategories' => [],
                            ],
                        ],
                    ],
                ],
            ],

            'table-decor' => [
                'name' => 'Table Decor',
                'slug' => 'table-decor',
                'image' => '/images/categories/table-decor.webp',
                'description' => 'Artisanal tablecloths, botanical runners & table mats',
                'subcategories' => [
                    'table-cloth' => [
                        'name' => 'Table Cloth',
                        'slug' => 'table-cloth',
                        'image' => '/images/products/table-cloth.webp',
                        'description' => 'Pure cotton & linen table covers with spill-resistant weave',
                        'subcategories' => [
                            '6-seater-table-cloth' => [
                                'name' => '6 Seater',
                                'slug' => '6-seater-table-cloth',
                                'image' => '/images/products/table-cloth.webp',
                                'description' => 'Tailored dimensions for 60x90 inch family dining tables',
                                'subcategories' => [],
                            ],
                            '8-seater-table-cloth' => [
                                'name' => '8 Seater',
                                'slug' => '8-seater-table-cloth',
                                'image' => '/images/products/table-cloth.webp',
                                'description' => 'Generous 60x108 inch drape coverage for large dinner parties',
                                'subcategories' => [],
                            ],
                        ],
                    ],
                    'table-runners' => [
                        'name' => 'Table Runners',
                        'slug' => 'table-runners',
                        'image' => '/images/products/table-runner.webp',
                        'description' => 'Handcrafted botanical runners with tassel finishes',
                        'subcategories' => [],
                    ],
                    'table-mat' => [
                        'name' => 'Table Mat',
                        'slug' => 'table-mat',
                        'image' => '/images/products/table-runner.webp',
                        'description' => 'Heat-resistant artisanal placemats and table mat sets',
                        'subcategories' => [],
                    ],
                ],
            ],

            'sofa-decor' => [
                'name' => 'Sofa Decor',
                'slug' => 'sofa-decor',
                'image' => '/images/categories/sofa-decor.webp',
                'description' => 'Cozy waffle throws & quilted anti-slip furniture protectors',
                'subcategories' => [
                    'sofa-throws' => [
                        'name' => 'Sofa Throws',
                        'slug' => 'sofa-throws',
                        'image' => '/images/products/sofa-throw.webp',
                        'description' => 'Soft textured waffle throws and knitted couch blankets',
                        'subcategories' => [],
                    ],
                    'sofa-protector' => [
                        'name' => 'Sofa Protector',
                        'slug' => 'sofa-protector',
                        'image' => '/images/products/sofa-cover.webp',
                        'description' => 'Quilted waterproof anti-slip sofa covers with utility pockets',
                        'subcategories' => [],
                    ],
                ],
            ],

            'towels-wellness' => [
                'name' => 'Towels & Wellness',
                'slug' => 'towels-wellness',
                'image' => '/images/categories/towels.webp',
                'description' => '600 GSM zero-twist Turkish terry bath & hand towels',
                'subcategories' => [
                    'bath-towels' => [
                        'name' => 'Bath Towels',
                        'slug' => 'bath-towels',
                        'image' => '/images/products/towels-luxury.webp',
                        'description' => 'Plush oversized bath sheets crafted from combed Turkish cotton',
                        'subcategories' => [],
                    ],
                    'hand-towels' => [
                        'name' => 'Hand Towels',
                        'slug' => 'hand-towels',
                        'image' => '/images/products/towels-ribbed.webp',
                        'description' => 'Quick-drying ribbed hand towels for everyday bathroom elegance',
                        'subcategories' => [],
                    ],
                    'kids-bath-towels' => [
                        'name' => 'Kids Bath Towels',
                        'slug' => 'kids-bath-towels',
                        'image' => '/images/products/towels-luxury.webp',
                        'description' => 'Hypoallergenic, ultra-gentle hooded towels for delicate young skin',
                        'subcategories' => [],
                    ],
                ],
            ],

            'rugs' => [
                'name' => 'Rugs',
                'slug' => 'rugs',
                'image' => '/images/categories/rugs.webp',
                'description' => 'Handcrafted wool, jute & natural fiber textured floor rugs',
                'subcategories' => [
                    'rugs-collection' => [
                        'name' => 'Rugs',
                        'slug' => 'rugs-collection',
                        'image' => '/images/products/rugs-artisan.webp',
                        'description' => 'Hand-tufted geometric weaves and earthy natural jute floor rugs',
                        'subcategories' => [],
                    ],
                ],
            ],
        ];
    }

    /**
     * Resolve a category node and its hierarchy metadata by URL path.
     * Path example: 'bedsheets-dohars/cotton-bedsheets'
     *
     * @return array{node: array<string, mixed>, path: string, ancestors: array<int, array<string, mixed>>, parent: ?array<string, mixed>, parent_path: ?string}|null
     */
    public static function resolveByPath(string $path): ?array
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        if (empty($segments)) {
            return null;
        }

        $hierarchy = self::getHierarchy();
        $currentLevel = $hierarchy;
        $ancestors = [];
        $currentNode = null;
        $accumulatedPath = '';
        $parent = null;
        $parentPath = null;

        foreach ($segments as $index => $segment) {
            $foundKey = null;

            // Direct key match or slug match
            if (isset($currentLevel[$segment])) {
                $foundKey = $segment;
            } else {
                foreach ($currentLevel as $key => $candidate) {
                    if (($candidate['slug'] ?? '') === $segment) {
                        $foundKey = $key;
                        break;
                    }
                }
            }

            if ($foundKey === null) {
                // Also check if segment is a descendant slug
                $deepFound = self::findDeepInSubtree($currentLevel, $segment);
                if ($deepFound !== null) {
                    return $deepFound;
                }

                return null;
            }

            $node = $currentLevel[$foundKey];
            $nodePath = $accumulatedPath === '' ? ($node['slug'] ?? $foundKey) : $accumulatedPath.'/'.$node['slug'];

            if ($index === count($segments) - 1) {
                $currentNode = $node;
                $accumulatedPath = $nodePath;
                break;
            }

            $parent = $node;
            $parentPath = $nodePath;
            $ancestors[] = [
                'name' => $node['name'],
                'slug' => $node['slug'],
                'path' => $nodePath,
                'url' => url('/categories/'.$nodePath),
            ];

            $accumulatedPath = $nodePath;
            $currentLevel = $node['subcategories'] ?? [];
        }

        if ($currentNode === null) {
            return null;
        }

        return [
            'node' => $currentNode,
            'path' => $accumulatedPath,
            'ancestors' => $ancestors,
            'parent' => $parent,
            'parent_path' => $parentPath,
        ];
    }

    /**
     * Recursively find a node by slug across the full hierarchy.
     *
     * @return array{node: array<string, mixed>, path: string, ancestors: array<int, array<string, mixed>>, parent: ?array<string, mixed>, parent_path: ?string}|null
     */
    public static function findBySlug(string $slug): ?array
    {
        return self::findDeepInSubtree(self::getHierarchy(), $slug);
    }

    /**
     * Internal recursive search helper.
     *
     * @param  array<string, array<string, mixed>>  $nodes
     * @param  array<int, array<string, mixed>>  $ancestors
     * @return array{node: array<string, mixed>, path: string, ancestors: array<int, array<string, mixed>>, parent: ?array<string, mixed>, parent_path: ?string}|null
     */
    private static function findDeepInSubtree(array $nodes, string $slug, array $ancestors = [], string $pathPrefix = '', ?array $parent = null, ?string $parentPath = null): ?array
    {
        foreach ($nodes as $key => $node) {
            $nodeSlug = $node['slug'] ?? $key;
            $nodePath = $pathPrefix === '' ? $nodeSlug : $pathPrefix.'/'.$nodeSlug;

            if ($nodeSlug === $slug || $key === $slug) {
                return [
                    'node' => $node,
                    'path' => $nodePath,
                    'ancestors' => $ancestors,
                    'parent' => $parent,
                    'parent_path' => $parentPath,
                ];
            }

            if (! empty($node['subcategories'])) {
                $nextAncestors = $ancestors;
                $nextAncestors[] = [
                    'name' => $node['name'],
                    'slug' => $nodeSlug,
                    'path' => $nodePath,
                    'url' => url('/categories/'.$nodePath),
                ];

                $found = self::findDeepInSubtree(
                    $node['subcategories'],
                    $slug,
                    $nextAncestors,
                    $nodePath,
                    $node,
                    $nodePath
                );

                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Build breadcrumbs array for a resolved category or path.
     *
     * @param  array<string, mixed>  $resolved
     * @return array<int, array{name: string, url: ?string, is_current: bool}>
     */
    public static function getBreadcrumbs(array $resolved): array
    {
        $breadcrumbs = [
            [
                'name' => 'Home',
                'url' => route('home'),
                'is_current' => false,
            ],
            [
                'name' => 'Shop by Category',
                'url' => url('/categories'),
                'is_current' => false,
            ],
        ];

        foreach ($resolved['ancestors'] ?? [] as $ancestor) {
            $breadcrumbs[] = [
                'name' => $ancestor['name'],
                'url' => $ancestor['url'] ?? url('/categories/'.$ancestor['path']),
                'is_current' => false,
            ];
        }

        $node = $resolved['node'] ?? [];
        $breadcrumbs[] = [
            'name' => $node['name'] ?? 'Category',
            'url' => null,
            'is_current' => true,
        ];

        return $breadcrumbs;
    }

    /**
     * Check if a category node is a leaf (has no subcategories).
     *
     * @param  array<string, mixed>  $node
     */
    public static function isLeaf(array $node): bool
    {
        return empty($node['subcategories']);
    }

    /**
     * Recursively collect all leaf slugs from a category node.
     *
     * @param  array<string, mixed>  $node
     * @return array<int, string>
     */
    public static function getAllLeafSlugs(array $node): array
    {
        if (self::isLeaf($node)) {
            return [$node['slug']];
        }

        $leafs = [];
        foreach ($node['subcategories'] ?? [] as $sub) {
            $leafs = array_merge($leafs, self::getAllLeafSlugs($sub));
        }

        return array_values(array_unique($leafs));
    }
}
