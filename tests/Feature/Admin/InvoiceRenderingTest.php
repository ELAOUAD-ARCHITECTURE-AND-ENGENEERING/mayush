<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_uses_clear_customer_shipping_and_seller_labels(): void
    {
        $customer = User::factory()->customer()->create(['name' => 'Client Person']);
        $seller = User::factory()->seller()->create(['name' => 'Seller Person']);
        Shop::factory()->create([
            'user_id' => $seller->id,
            'name' => 'Mayush Seller Studio',
        ]);
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'name' => 'Invoice Label Vase',
        ]);
        $order = $this->orderWithAddresses($customer, $seller);
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'product_name' => 'Invoice Label Vase',
            'quantity' => 1,
            'price' => 120,
            'tax' => 10,
            'shipping_cost' => 15,
        ]);

        $html = $this->renderInvoice($order->fresh(['orderDetails.product', 'shop']));

        $this->assertStringContainsString('Client / Billing address', $html);
        $this->assertStringContainsString('Billing Buyer', $html);
        $this->assertStringContainsString('Shipping address', $html);
        $this->assertStringContainsString('Shipping Receiver', $html);
        $this->assertStringContainsString('Seller', $html);
        $this->assertStringContainsString('Mayush Seller Studio', $html);
        $this->assertStringContainsString('Invoice Label Vase', $html);
    }

    public function test_invoice_displays_order_detail_product_name_when_product_relation_is_missing(): void
    {
        $customer = User::factory()->customer()->create();
        $seller = User::factory()->seller()->create();
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'name' => 'Deleted Relation Lamp',
        ]);
        $order = $this->orderWithAddresses($customer, $seller);
        OrderDetail::factory()->create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'product_name' => 'Deleted Relation Lamp Snapshot',
            'quantity' => 2,
            'price' => 80,
            'tax' => 8,
            'shipping_cost' => 12,
        ]);

        $product->delete();

        $html = $this->renderInvoice($order->fresh(['orderDetails.product', 'shop']));

        $this->assertStringContainsString('Deleted Relation Lamp Snapshot', $html);
        $this->assertStringNotContainsString('Product #' . $product->id, $html);
    }

    private function orderWithAddresses(User $customer, User $seller): Order
    {
        return Order::factory()->create([
            'user_id' => $customer->id,
            'seller_id' => $seller->id,
            'code' => 'INV-1001',
            'date' => now()->timestamp,
            'shipping_address' => json_encode([
                'name' => 'Shipping Receiver',
                'email' => 'shipping@example.test',
                'phone' => '222-222-2222',
                'address' => '22 Shipping Road',
                'city' => 'Ship City',
                'state' => 'Ship State',
                'postal_code' => '22000',
                'country' => 'MA',
            ]),
            'billing_address' => json_encode([
                'name' => 'Billing Buyer',
                'email' => 'billing@example.test',
                'phone' => '111-111-1111',
                'address' => '11 Billing Street',
                'city' => 'Bill City',
                'state' => 'Bill State',
                'postal_code' => '11000',
                'country' => 'MA',
            ]),
            'payment_type' => 'cash_on_delivery',
            'shipping_type' => 'home_delivery',
            'grand_total' => 145,
        ]);
    }

    private function renderInvoice(Order $order): string
    {
        return view('backend.invoices.invoice', [
            'order' => $order,
            'font_family' => "'Roboto','sans-serif'",
            'direction' => 'ltr',
            'text_align' => 'left',
            'not_text_align' => 'right',
        ])->render();
    }
}
