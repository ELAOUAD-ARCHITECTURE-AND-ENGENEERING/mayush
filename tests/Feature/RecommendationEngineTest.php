<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\CombinedOrder;
use App\Models\FrequentlyBoughtProduct;
use App\Jobs\ProcessFrequentlyBoughtJob;

class RecommendationEngineTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_associates_products_bought_together_frequently()
    {
        try {
            // 1. Setup Base Data
            $seller = User::factory()->seller()->create();
            $customer = User::factory()->customer()->create();

            // 2. Setup Products
            $productA = Product::factory()->create(['user_id' => $seller->id, 'name' => 'Product A']);
            $productB = Product::factory()->create(['user_id' => $seller->id, 'name' => 'Product B']);
            $productC = Product::factory()->create(['user_id' => $seller->id, 'name' => 'Product C']);

            // 3. Setup Orders (A + B bought together twice, A + C once)
            $this->createOrderWithProducts($customer, [$productA, $productB]);
            $this->createOrderWithProducts($customer, [$productA, $productB]);
            $this->createOrderWithProducts($customer, [$productA, $productC]);

            // 4. Run the Job (Threshold 2)
            (new ProcessFrequentlyBoughtJob(2))->handle();

            // 5. Assert Associations
            $this->assertDatabaseHas('frequently_bought_products', [
                'product_id' => $productA->id,
                'frequently_bought_product_id' => $productB->id
            ]);

            $this->assertDatabaseMissing('frequently_bought_products', [
                'product_id' => $productA->id,
                'frequently_bought_product_id' => $productC->id
            ]);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    protected function createOrderWithProducts($customer, $products)
    {
        $combinedOrder = new CombinedOrder();
        $combinedOrder->user_id = $customer->id;
        $combinedOrder->shipping_address = 'Test Address';
        $combinedOrder->grand_total = 100;
        $combinedOrder->save();

        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'combined_order_id' => $combinedOrder->id,
            'payment_status' => 'paid',
            'delivery_status' => 'delivered'
        ]);

        foreach ($products as $product) {
            $orderDetail = new OrderDetail();
            $orderDetail->order_id = $order->id;
            $orderDetail->product_id = $product->id;
            $orderDetail->seller_id = $product->user_id;
            $orderDetail->price = $product->unit_price ?? 10;
            $orderDetail->tax = 0;
            $orderDetail->shipping_cost = 0;
            $orderDetail->quantity = 1;
            $orderDetail->payment_status = 'paid';
            $orderDetail->delivery_status = 'delivered';
            $orderDetail->save();
        }

        return $order;
    }
}
