<?php

namespace Tests\Feature\Frontend;

use Tests\TestCase;

class ProductCardHoverStyleTest extends TestCase
{
    public function test_product_card_hover_border_is_applied_inside_the_card(): void
    {
        $css = file_get_contents(public_path('assets/css/custom-style.css'));

        $this->assertStringContainsString('.aiz-card-box:hover', $css);
        $this->assertStringContainsString('.aiz-card-box:hover::before', $css);
        $this->assertStringContainsString('.aiz-card-box:hover::after', $css);
        $this->assertStringContainsString('z-index: 30;', $css);
        $this->assertStringContainsString('border-top-color: var(--primary)', $css);
        $this->assertStringContainsString('border-right-color: var(--primary)', $css);
        $this->assertStringContainsString('border-bottom-color: var(--primary)', $css);
        $this->assertStringContainsString('border-left-color: var(--primary)', $css);
        $this->assertStringContainsString('width 0.3s ease-out', $css);
        $this->assertStringContainsString('.hov-animate-outline:has(.aiz-card-box)::before', $css);
        $this->assertStringContainsString('display: none !important;', $css);
    }
}
