<?php

namespace Tests\Feature\Frontend;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerOrderPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_repayment_rejects_unavailable_payment_method_without_changing_order(): void
    {
        $user = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'unpaid',
            'delivery_status' => 'pending',
            'manual_payment' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('order.re_payment'), [
            'order_id' => $order->id,
            'payment_option' => 'unsupported_gateway',
        ]);

        $response->assertRedirect();
        $this->assertSame('unpaid', $order->fresh()->payment_status);
        $this->assertSame(0, (int) $order->fresh()->manual_payment);
    }

    public function test_order_repayment_route_is_available_from_the_buyer_flow(): void
    {
        $view = file_get_contents(resource_path('views/frontend/user/order_details_customer.blade.php'));

        $this->assertStringContainsString("route('order.re_payment')", $view);
        $this->assertStringContainsString("get_setting('cmi_payment')", $view);
        $this->assertStringContainsString('No online payment method is available', $view);
    }
}
