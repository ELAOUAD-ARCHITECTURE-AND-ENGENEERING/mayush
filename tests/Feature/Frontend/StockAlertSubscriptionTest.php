<?php

namespace Tests\Feature\Frontend;

use App\Models\Product;
use App\Models\StockSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class StockAlertSubscriptionTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_guest_can_subscribe_with_email(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->postJson(route('stock.alert.subscribe'), [
            'product_id' => $product->id,
            'email' => 'buyer@example.test',
        ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('stock_subscriptions', [
            'product_id' => $product->id,
            'email' => 'buyer@example.test',
            'notified_at' => null,
        ]);
    }

    public function test_guest_email_is_required(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->postJson(route('stock.alert.subscribe'), [
            'product_id' => $product->id,
        ])->assertUnprocessable();

        $this->assertSame(0, StockSubscription::count());
    }

    public function test_authenticated_user_can_subscribe_without_entering_email(): void
    {
        $user = User::factory()->customer()->create(['email' => 'customer@example.test']);
        $product = Product::factory()->outOfStock()->create();

        $this->actingAs($user)
            ->postJson(route('stock.alert.subscribe'), [
                'product_id' => $product->id,
            ])
            ->assertOk()
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('stock_subscriptions', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'email' => 'customer@example.test',
        ]);
    }

    public function test_duplicate_subscription_is_handled_gracefully(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $payload = [
            'product_id' => $product->id,
            'email' => 'buyer@example.test',
        ];

        $this->postJson(route('stock.alert.subscribe'), $payload)->assertOk();
        $this->postJson(route('stock.alert.subscribe'), $payload)
            ->assertOk()
            ->assertJson(['status' => 'info']);

        $this->assertSame(1, StockSubscription::where('product_id', $product->id)->where('email', 'buyer@example.test')->count());
    }

    public function test_out_of_stock_partial_contains_subscription_form(): void
    {
        $markup = file_get_contents(resource_path('views/frontend/partials/addToCart.blade.php'));

        $this->assertStringContainsString("route('stock.alert.subscribe')", $markup);
        $this->assertStringContainsString('Notify me when available', $markup);
        $this->assertStringContainsString('name="product_id"', $markup);
    }
}
