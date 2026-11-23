<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'parent_slug',
        'path',
        'image',
        'description',
        'badge',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Parent category relationship.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Immediate child subcategories.
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    /**
     * Alias for subcategories.
     */
    public function children(): HasMany
    {
        return $this->subcategories();
    }

    /**
     * Recursive relationship to load all descendant subcategories.
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Products belonging to this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id')->orderBy('sort_order', 'asc');
    }

    /**
     * Check if category is a root level category.
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if category is a leaf node (has no subcategories).
     */
    public function isLeaf(): bool
    {
        return $this->subcategories()->count() === 0;
    }

    /**
     * Determine category hierarchy depth level (0 = Main, 1 = Subcategory, 2 = Sub-sub, etc.).
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $current = $this->parent;
        while ($current) {
            $depth++;
            $current = $current->parent;
        }

        return $depth;
    }

    /**
     * Get ancestors collection from root to immediate parent.
     *
     * @return Collection<int, Category>
     */
    public function getAncestorsAttribute(): Collection
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    /**
     * Recursively rebuild hierarchical path strings for this category and its descendants.
     */
    public function rebuildNestedPaths(): void
    {
        $parent = $this->parent;
        $this->parent_slug = $parent?->slug;
        $this->path = $parent ? "{$parent->path}/{$this->slug}" : $this->slug;
        $this->saveQuietly();

        foreach ($this->subcategories as $child) {
            $child->rebuildNestedPaths();
        }
    }
}
