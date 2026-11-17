<?php

namespace Tests\Unit;

use App\Services\CategoryService;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    public function test_get_hierarchy_contains_all_six_main_categories(): void
    {
        $hierarchy = CategoryService::getHierarchy();

        $this->assertArrayHasKey('curtains-drapes', $hierarchy);
        $this->assertArrayHasKey('table-decor', $hierarchy);
        $this->assertArrayHasKey('sofa-decor', $hierarchy);
        $this->assertArrayHasKey('towels-wellness', $hierarchy);
        $this->assertArrayHasKey('bedsheets-dohars', $hierarchy);
        $this->assertArrayHasKey('rugs', $hierarchy);
    }

    public function test_resolve_by_path_for_main_category(): void
    {
        $resolved = CategoryService::resolveByPath('bedsheets-dohars');

        $this->assertNotNull($resolved);
        $this->assertSame('Bedsheets & Dohars', $resolved['node']['name']);
        $this->assertFalse(CategoryService::isLeaf($resolved['node']));
        $this->assertCount(5, $resolved['node']['subcategories']);
    }

    public function test_resolve_by_path_for_subcategory(): void
    {
        $resolved = CategoryService::resolveByPath('bedsheets-dohars/cotton-bedsheets');

        $this->assertNotNull($resolved);
        $this->assertSame('Cotton Bedsheets', $resolved['node']['name']);
        $this->assertFalse(CategoryService::isLeaf($resolved['node']));
        $this->assertArrayHasKey('king-size-cotton-bedsheets', $resolved['node']['subcategories']);
    }

    public function test_resolve_by_path_for_leaf_category(): void
    {
        $resolved = CategoryService::resolveByPath('bedsheets-dohars/cotton-bedsheets/king-size-cotton-bedsheets');

        $this->assertNotNull($resolved);
        $this->assertSame('King Size', $resolved['node']['name']);
        $this->assertTrue(CategoryService::isLeaf($resolved['node']));
    }

    public function test_find_by_slug(): void
    {
        $resolved = CategoryService::findBySlug('king-size-cotton-bedsheets');

        $this->assertNotNull($resolved);
        $this->assertSame('King Size', $resolved['node']['name']);
        $this->assertSame('bedsheets-dohars/cotton-bedsheets/king-size-cotton-bedsheets', $resolved['path']);
    }
}
