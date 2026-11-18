<?php

namespace Tests\Feature;

use Tests\TestCase;

class HeroCarouselTest extends TestCase
{
    /**
     * Test the Hero section renders the new interactive infinite carousel structure.
     */
    public function test_hero_carousel_renders_with_interactive_structure(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Verify data attributes for the JavaScript carousel controller
        $response->assertSee('data-hero-carousel', false);
        $response->assertSee('data-carousel-track', false);
        $response->assertSee('data-carousel-group="0"', false);
        $response->assertSee('data-carousel-group="1"', false);
        $response->assertSee('data-carousel-group="2"', false);

        // Verify touch-action class and drag prevention attributes
        $response->assertSee('touch-pan-y', false);
        $response->assertSee('select-none', false);
        $response->assertSee('cursor-grab', false);
        $response->assertSee('draggable="false"', false);

        // Verify hover pause is removed
        $response->assertDontSee('hover:[animation-play-state:paused]', false);
    }

    /**
     * Test product cards inside the hero carousel contain correct product URLs and metadata.
     */
    public function test_hero_carousel_cards_link_to_correct_product_pages(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Verify product detail links for hero products
        $response->assertSee(route('products.show', 1), false);
        $response->assertSee(route('products.show', 2), false);
        $response->assertSee(route('products.show', 3), false);
        $response->assertSee(route('products.show', 5), false);
        $response->assertSee(route('products.show', 7), false);

        // Verify key spotlight product names and prices
        $response->assertSee('Custom Linen Drapes');
        $response->assertSee('Embroidered Bed Linen');
        $response->assertSee('600 GSM Combed Cotton');
        $response->assertSee('Artisanal Mulmul Dohars');
        $response->assertSee('Handcrafted Table Runner');
    }

    /**
     * Test accessibility: duplicate buffer sets have aria-hidden and tabindex=-1.
     */
    public function test_hero_carousel_accessibility_attributes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Verify aria-hidden and tabindex="-1" exist on cloned buffer groups
        $response->assertSee('aria-hidden="true"', false);
        $response->assertSee('tabindex="-1"', false);
    }
}
