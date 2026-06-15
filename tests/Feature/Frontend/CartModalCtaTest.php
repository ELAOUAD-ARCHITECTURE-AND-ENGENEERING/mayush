<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;

class CartModalCtaTest extends TestCase
{
    public function test_added_to_cart_modal_labels_match_destinations(): void
    {
        $markup = file_get_contents(resource_path('views/frontend/partials/addedToCart.blade.php'));

        $this->assertStringContainsString("route('cart')", $markup);
        $this->assertStringContainsString("translate('View Cart')", $markup);
        $this->assertStringContainsString("route('checkout.shipping_info')", $markup);
        $this->assertStringContainsString("translate('Proceed to Checkout')", $markup);
        $this->assertStringNotContainsString('<a href="{{ route(\'cart\') }}" class="btn btn-primary mb-3 mb-sm-0">{{ translate(\'Proceed to Checkout\')}}</a>', $markup);
    }
}
