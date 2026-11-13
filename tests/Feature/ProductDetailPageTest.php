<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductDetailPageTest extends TestCase
{
    /**
     * Test the landing page loads successfully with contact section.
     */
    public function test_home_page_loads_with_contact_section(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Handcrafted Bedsheets & Mulmul Dohars');
        $response->assertSee('Rooted in Simple Living, Crafted for Everyday Comfort');
        $response->assertSee('Trending Home Essentials');
        $response->assertSee('Make Your Home More Beautiful');
    }

    /**
     * Test the product catalog page loads successfully.
     */
    public function test_product_catalog_page_loads(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Menahem Collection');
    }

    /**
     * Test product detail page loads with accurate product info.
     */
    public function test_product_detail_page_loads_successfully(): void
    {
        $response = $this->get('/products/1');

        $response->assertStatus(200);
        $response->assertSee('Cotton Bedsheet & Dohar Set');
        $response->assertSee('Buy via WhatsApp');
        $response->assertSee('Add to Bag');
    }
}
