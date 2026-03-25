<?php

namespace Tests\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class XssProtectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_names_are_escaped_in_views()
    {
        $xss_payload = "<script>alert('xss')</script>";
        $product = Product::factory()->create(['name' => $xss_payload, 'published' => 1, 'approved' => 1]);

        $response = $this->get(route('product', $product->slug));

        $response->assertStatus(200);
        // The raw script should NOT be present in the output
        $response->assertDontSee($xss_payload, false); // false = don't escape the search string itself
        // It should be escaped
        $response->assertSee(e($xss_payload), false);
    }

    /** @test */
    public function search_results_escape_query_string()
    {
        $xss_payload = '"><img src=x onerror=alert(1)>';
        
        $response = $this->get(route('search', ['q' => $xss_payload]));

        $response->assertStatus(200);
        $response->assertDontSee($xss_payload, false);
    }
}
