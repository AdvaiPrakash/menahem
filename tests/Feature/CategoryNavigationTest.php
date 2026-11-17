<?php

namespace Tests\Feature;

use Tests\TestCase;

class CategoryNavigationTest extends TestCase
{
    /**
     * Test the root Shop by Category page displays all 6 primary categories.
     */
    public function test_shop_by_category_index_page_loads_with_all_six_categories(): void
    {
        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Shop by Category');
        $response->assertSee('Curtains & Drapes');
        $response->assertSee('Table Decor');
        $response->assertSee('Sofa Decor');
        $response->assertSee('Towels & Wellness');
        $response->assertSee('Bedsheets & Dohars');
        $response->assertSee('Rugs');
    }

    /**
     * Test navigating to a main category displays its subcategories.
     */
    public function test_category_level_one_loads_subcategories(): void
    {
        $response = $this->get('/categories/bedsheets-dohars');

        $response->assertStatus(200);
        $response->assertSee('Bedsheets & Dohars');
        $response->assertSee('Cotton Bedsheets');
        $response->assertSee('Fitted Bedsheets');
        $response->assertSee('Embroidered Bedsheets');
        $response->assertSee('Dohars');
        $response->assertSee('Comforters');
        $response->assertSee('Back to All Categories');
    }

    /**
     * Test navigating to a subcategory displays the next level of options.
     */
    public function test_category_level_two_loads_further_subcategories(): void
    {
        $response = $this->get('/categories/bedsheets-dohars/cotton-bedsheets');

        $response->assertStatus(200);
        $response->assertSee('Cotton Bedsheets');
        $response->assertSee('King Size');
        $response->assertSee('Queen Size');
        $response->assertSee('Single');
        $response->assertSee('Back to Bedsheets & Dohars');
    }

    /**
     * Test that clicking or visiting a final leaf category redirects to the filtered products page.
     */
    public function test_leaf_category_redirects_to_product_listing_page(): void
    {
        $response = $this->get('/categories/bedsheets-dohars/cotton-bedsheets/king-size-cotton-bedsheets');

        $response->assertRedirect('/products?category=king-size-cotton-bedsheets');
    }

    /**
     * Test product listing page shows correct category context, breadcrumbs and back link.
     */
    public function test_leaf_category_renders_on_product_listing_page(): void
    {
        $response = $this->get('/products?category=king-size-cotton-bedsheets');

        $response->assertStatus(200);
        $response->assertSee('King Size');
        $response->assertSee('Cotton Bedsheets');
        $response->assertSee('Back to Cotton Bedsheets');
        $response->assertSee('Cotton Bedsheet & Dohar Set');
    }

    /**
     * Test Curtains & Drapes multi-level navigation.
     */
    public function test_curtains_hierarchy_navigation(): void
    {
        // Level 1: Curtains & Drapes
        $res1 = $this->get('/categories/curtains-drapes');
        $res1->assertStatus(200);
        $res1->assertSee('Sheer Curtains');
        $res1->assertSee('Blackout Curtains');

        // Level 2: Sheer Curtains
        $res2 = $this->get('/categories/curtains-drapes/sheer-curtains');
        $res2->assertStatus(200);
        $res2->assertSee('Ready-made Curtains');
        $res2->assertSee('Customization');
        $res2->assertSee('Back to Curtains & Drapes');

        // Level 3: Leaf category redirect
        $res3 = $this->get('/categories/curtains-drapes/sheer-curtains/ready-made-sheer-curtains');
        $res3->assertRedirect('/products?category=ready-made-sheer-curtains');
    }

    /**
     * Test homepage Category Section links directly to multi-level category navigation.
     */
    public function test_homepage_category_section_links_to_multi_level_category_pages(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('/categories/curtains-drapes');
        $response->assertSee('/categories/table-decor');
        $response->assertSee('/categories/sofa-decor');
        $response->assertSee('/categories/towels-wellness');
        $response->assertSee('/categories/bedsheets-dohars');
        $response->assertSee('/categories/rugs');
    }
}
