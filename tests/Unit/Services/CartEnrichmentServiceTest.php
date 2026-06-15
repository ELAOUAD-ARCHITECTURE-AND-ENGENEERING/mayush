<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CartEnrichmentService;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Auth;
use Session;

class CartEnrichmentServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_empty_collection_when_cart_is_empty(): void
    {
        // Arrange
        $user = User::factory()->create();
        Auth::login($user);

        // Act
        $suggestions = CartEnrichmentService::getSuggestions(2);

        // Assert
        $this->assertTrue($suggestions->isEmpty());
    }

    /** @test */
    public function it_gets_suggestions_for_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        Auth::login($user);

        $productInCart = Product::factory()->create([
            'tags' => 'modern,accent',
            'published' => 1,
            'approved' => 1
        ]);

        $suggestedProduct = Product::factory()->create([
            'tags' => 'modern',
            'published' => 1,
            'approved' => 1
        ]);

        Cart::create([
            'user_id' => $user->id,
            'product_id' => $productInCart->id,
            'quantity' => 1,
            'price' => 100,
            'status' => 1
        ]);

        // Act
        $suggestions = CartEnrichmentService::getSuggestions(2);

        // Assert
        $this->assertCount(1, $suggestions);
        $this->assertEquals($suggestedProduct->id, $suggestions->first()->id);
    }
}

