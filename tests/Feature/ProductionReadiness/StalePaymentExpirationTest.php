<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\PaymentAttempt;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\Traits\SeedsAppConfigs;

class StalePaymentExpirationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function it_expires_initiated_payments_older_than_60_minutes_and_restores_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['num_of_sale' => 10, 'digital' => 0]);
        $stock = ProductStock::factory()->create([
            'product_id' => $product->id,
            'qty' => 5,
            'variant' => ''
        ]);

        $combinedOrder = CombinedOrder::factory()->create(['user_id' => $user->id]);
        $order = Order::factory()->create([
            'combined_order_id' => $combinedOrder->id,
            'user_id' => $user->id,
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending'
        ]);
        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'delivery_status' => 'pending',
            'variation' => ''
        ]);

        $attempt = PaymentAttempt::create([
            'user_id' => $user->id,
            'combined_order_id' => $combinedOrder->id,
            'payment_method' => 'cmi',
            'status' => 'initiated',
            'initiated_at' => Carbon::now()->subMinutes(65),
            'amount' => 100
        ]);

        Artisan::call('mayush:payments:expire-stale');

        // Assert payment attempt is expired
        $this->assertEquals('expired', $attempt->fresh()->status);

        // Assert order is cancelled
        $this->assertEquals('cancelled', $order->fresh()->delivery_status);
        $this->assertEquals('cancelled', $orderDetail->fresh()->delivery_status);

        // Assert stock is restored
        $this->assertEquals(7, $stock->fresh()->qty);
        $this->assertEquals(8, $product->fresh()->num_of_sale); // 10 - 2 = 8

        // Assert inventory log
        $this->assertDatabaseHas('inventory_logs', [
            'product_id' => $product->id,
            'quantity_delta' => 2,
            'reason' => 'auto_cancelled_stale_payment'
        ]);
    }

    /** @test */
    public function it_does_not_expire_recently_initiated_payments(): void
    {
        $attempt = PaymentAttempt::create([
            'payment_method' => 'cmi',
            'status' => 'initiated',
            'initiated_at' => Carbon::now()->subMinutes(30),
            'amount' => 100
        ]);

        Artisan::call('mayush:payments:expire-stale');

        $this->assertEquals('initiated', $attempt->fresh()->status);
    }

    /** @test */
    public function it_does_not_cancel_orders_if_they_are_paid(): void
    {
        $combinedOrder = CombinedOrder::factory()->create();
        $order = Order::factory()->create([
            'combined_order_id' => $combinedOrder->id,
            'payment_status' => 'paid',
            'delivery_status' => 'pending'
        ]);

        $attempt = PaymentAttempt::create([
            'combined_order_id' => $combinedOrder->id,
            'payment_method' => 'cmi',
            'status' => 'initiated',
            'initiated_at' => Carbon::now()->subMinutes(65),
            'amount' => 100
        ]);

        Artisan::call('mayush:payments:expire-stale');

        $this->assertEquals('expired', $attempt->fresh()->status);
        $this->assertEquals('pending', $order->fresh()->delivery_status); // Did not cancel
    }

    /** @test */
    public function running_expire_stale_command_twice_is_idempotent(): void
    {
        $product = Product::factory()->create(['num_of_sale' => 10, 'digital' => 0]);
        $stock = ProductStock::factory()->create(['product_id' => $product->id, 'qty' => 5]);

        $combinedOrder = CombinedOrder::factory()->create();
        $order = Order::factory()->create(['combined_order_id' => $combinedOrder->id, 'payment_status' => 'unpaid']);
        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'variation' => ''
        ]);

        PaymentAttempt::create([
            'combined_order_id' => $combinedOrder->id,
            'payment_method' => 'cmi',
            'status' => 'initiated',
            'initiated_at' => Carbon::now()->subMinutes(65),
            'amount' => 100
        ]);

        Artisan::call('mayush:payments:expire-stale');
        $this->assertEquals(7, $stock->fresh()->qty);

        // Run again
        Artisan::call('mayush:payments:expire-stale');
        $this->assertEquals(7, $stock->fresh()->qty); // Doesn't restore twice
    }

    /** @test */
    public function product_num_of_sale_never_becomes_negative(): void
    {
        $product = Product::factory()->create(['num_of_sale' => 1, 'digital' => 0]); // Under-allocated compared to order
        $stock = ProductStock::factory()->create(['product_id' => $product->id, 'qty' => 5, 'variant' => '']);

        $combinedOrder = CombinedOrder::factory()->create();
        $order = Order::factory()->create(['combined_order_id' => $combinedOrder->id, 'payment_status' => 'unpaid']);
        OrderDetail::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'quantity' => 5, 'variation' => '']);

        PaymentAttempt::create([
            'combined_order_id' => $combinedOrder->id,
            'status' => 'initiated',
            'initiated_at' => Carbon::now()->subMinutes(65)
        ]);

        Artisan::call('mayush:payments:expire-stale');

        $this->assertEquals(0, $product->fresh()->num_of_sale); // 1 - 5 = max(0, -4) = 0
    }
}
