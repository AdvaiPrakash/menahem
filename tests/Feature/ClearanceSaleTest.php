<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClearanceSaleTest extends TestCase
{
    /**
     * Test that the home page displays the clearance sale section under the essence section.
     */
    public function test_home_page_displays_clearance_sale_section_with_specified_percent_off(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Asserts presence of Clearance Sale section
        $response->assertSee('Clearance Sale');
        $response->assertSee('Limited Stock Clearance');

        // Asserts specified percent off badges are displayed
        $response->assertSee('50% OFF');
        $response->assertSee('45% OFF');
        $response->assertSee('44% OFF');

        // Asserts savings and prices
        $response->assertSee('Save ₹1,000 (50% OFF)');
        $response->assertSee('Save ₹900 (45% OFF)');

        // Asserts section order: "Our Essence & Story" appears before "Clearance Sale"
        $content = $response->getContent();
        $essencePos = strpos($content, 'Our Essence');
        $clearancePos = strpos($content, 'Clearance Sale');

        $this->assertNotFalse($essencePos, 'Our Essence & Story section was not found.');
        $this->assertNotFalse($clearancePos, 'Clearance Sale section was not found.');
        $this->assertTrue($essencePos < $clearancePos, 'Clearance Sale must appear after Our Essence & Story section.');
    }

    /**
     * Test that clearance sale items contain cart dispatch and WhatsApp links.
     */
    public function test_clearance_sale_items_have_action_buttons_and_cart_dispatch(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Asserts add-to-cart dispatch trigger
        $response->assertSee('$dispatch(\'add-to-cart\'', false);

        // Asserts WhatsApp order links for quick purchase
        $response->assertSee('https://wa.me/', false);
        $response->assertSee('Buy Now');
    }
}
