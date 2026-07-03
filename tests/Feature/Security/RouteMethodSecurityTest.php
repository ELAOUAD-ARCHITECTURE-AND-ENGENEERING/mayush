<?php

namespace Tests\Feature\Security;

use App\Models\Address;
use App\Models\CustomerProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class RouteMethodSecurityTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedConfigs();
    }

    public function test_address_default_and_billing_routes_reject_get_and_scope_to_owner(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        $ownAddress = Address::factory()->create(['user_id' => $customer->id]);
        $otherAddress = Address::factory()->create(['user_id' => $otherCustomer->id]);

        $this->actingAs($customer)
            ->get(route('addresses.set_default', $ownAddress->id))
            ->assertNotFound();

        $this->actingAs($customer)
            ->post(route('addresses.set_default', $ownAddress->id))
            ->assertRedirect();

        $this->assertSame(1, (int) $ownAddress->fresh()->set_default);

        $this->actingAs($customer)
            ->post(route('addresses.set_billing', $otherAddress->id))
            ->assertForbidden();

        $this->assertSame(0, (int) $otherAddress->fresh()->set_billing);
    }

    public function test_address_delete_uses_delete_method_and_blocks_other_users_address(): void
    {
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();

        $ownAddress = Address::factory()->create(['user_id' => $customer->id]);
        $otherAddress = Address::factory()->create(['user_id' => $otherCustomer->id]);

        $this->actingAs($customer)
            ->delete(route('addresses.destroy', $otherAddress->id))
            ->assertForbidden();

        $this->actingAs($customer)
            ->delete(route('addresses.destroy', $ownAddress->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('addresses', ['id' => $ownAddress->id]);
        $this->assertDatabaseHas('addresses', ['id' => $otherAddress->id]);
    }

    public function test_seller_address_default_and_delete_are_post_delete_and_owner_scoped(): void
    {
        $seller = User::factory()->seller()->create();
        $otherSeller = User::factory()->seller()->create();

        $ownAddress = Address::factory()->create(['user_id' => $seller->id]);
        $otherAddress = Address::factory()->create(['user_id' => $otherSeller->id]);

        $this->actingAs($seller)
            ->get(route('seller.addresses.set_default', $ownAddress->id))
            ->assertNotFound();

        $this->actingAs($seller)
            ->post(route('seller.addresses.set_default', $otherAddress->id))
            ->assertForbidden();

        $this->actingAs($seller)
            ->post(route('seller.addresses.set_default', $ownAddress->id))
            ->assertRedirect();

        $this->actingAs($seller)
            ->delete(route('seller.addresses.destroy', $otherAddress->id))
            ->assertForbidden();

        $this->assertSame(1, (int) $ownAddress->fresh()->set_default);
        $this->assertDatabaseHas('addresses', ['id' => $otherAddress->id]);
    }

    public function test_purchase_cancel_rejects_get_and_blocks_other_customers_order(): void
    {
        Mail::fake();

        User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();
        $otherCustomer = User::factory()->customer()->create();
        $seller = User::factory()->seller()->create();
        Shop::factory()->create(['user_id' => $seller->id]);

        $ownOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'seller_id' => $seller->id,
            'delivery_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);
        $otherOrder = Order::factory()->create([
            'user_id' => $otherCustomer->id,
            'seller_id' => $seller->id,
            'delivery_status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $product = Product::factory()->create(['user_id' => $seller->id]);
        OrderDetail::factory()->create([
            'order_id' => $ownOrder->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
        ]);

        $this->actingAs($customer)
            ->get(route('purchase_history.destroy', $ownOrder->id))
            ->assertNotFound();

        $this->actingAs($customer)
            ->post(route('purchase_history.destroy', $otherOrder->id))
            ->assertNotFound();

        $this->actingAs($customer)
            ->post(route('purchase_history.destroy', $ownOrder->id))
            ->assertRedirect();

        $this->assertSame('cancelled', $ownOrder->fresh()->delivery_status);
        $this->assertSame('pending', $otherOrder->fresh()->delivery_status);
    }

    public function test_customer_product_destroy_requires_delete_and_owner(): void
    {
        $seller = User::factory()->seller()->create();
        $otherSeller = User::factory()->seller()->create();

        $ownProduct = $this->customerProductFor($seller);
        $otherProduct = $this->customerProductFor($otherSeller);

        $this->actingAs($seller)
            ->get(route('customer_products.destroy', $ownProduct->id))
            ->assertNotFound();

        $this->actingAs($seller)
            ->delete(route('customer_products.destroy', $otherProduct->id))
            ->assertForbidden();

        $this->actingAs($seller)
            ->delete(route('customer_products.destroy', $ownProduct->id))
            ->assertRedirect(route('customer_products.index'));

        $this->assertDatabaseMissing('customer_products', ['id' => $ownProduct->id]);
        $this->assertDatabaseHas('customer_products', ['id' => $otherProduct->id]);
    }

    private function customerProductFor(User $seller): CustomerProduct
    {
        $product = new CustomerProduct();
        $product->name = 'Security Classified Product';
        $product->user_id = $seller->id;
        $product->added_by = 'seller';
        $product->conditon = 'new';
        $product->location = 'Casablanca';
        $product->unit = 'pc';
        $product->unit_price = 100;
        $product->slug = 'security-classified-' . $seller->id;
        $product->save();

        return $product;
    }
}
