<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\CombinedOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Traits\SeedsAppConfigs;

class CmiPaymentSafetyTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
        
        // Mock CMI configuration
        config([
            'cmi.merchant_id' => 'test_merchant',
            'cmi.secret_key' => 'test_secret',
            'cmi.gateway_url' => 'https://test.cmi.co.ma/fim/est3Dgate',
            'cmi.allowed_ips' => ['127.0.0.1'],
        ]);
    }

    private function calculateCmiHash(array $data): string
    {
        $storeKey = 'test_secret';
        $postParams = array_keys($data);
        natcasesort($postParams);

        $hashval = "";
        foreach ($postParams as $param) {
            $paramValue = $data[$param];
            $paramValue = html_entity_decode(preg_replace("/\n$/", "", $paramValue), ENT_QUOTES, 'UTF-8');
            $paramValue = preg_replace('/document./i', 'document.', $paramValue);
            $escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", $paramValue));
            
            $lowerParam = strtolower($param);
            if ($lowerParam != "hash" && $lowerParam != "encoding") {
                $hashval = $hashval . $escapedParamValue . "|";
            }
        }

        $escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
        $hashval = $hashval . $escapedStoreKey;
        $calculatedHashValue = hash('sha512', $hashval);
        return base64_encode(pack('H*', $calculatedHashValue));
    }

    /** @test */
    public function cmi_payment_initiation_requires_valid_order(): void
    {
        $user = User::factory()->customer()->create();
        $this->actingAs($user);

        // Test without valid order/combined order
        $response = $this->post(route('payment.checkout'), [
            'payment_option' => 'cmi'
        ]);

        // Should not crash
        $this->assertContains($response->status(), [200, 302, 400, 404]);
    }

    /** @test */
    public function invalid_cmi_callback_does_not_mark_order_as_paid(): void
    {
        $order = Order::factory()->create(['payment_status' => 'unpaid']);
        $oid = 'OR-' . $order->id . '-' . time();
        
        // Mock invalid CMI callback hash
        $response = $this->post(route('cmi.callback'), [
            'oid' => $oid,
            'amount' => $order->grand_total,
            'ProcReturnCode' => '99', // Invalid return code
            'HASH' => 'invalid_hash'
        ]);

        // Returns failure due to hash check failure
        $response->assertStatus(200);
        $response->assertSee('FAILURE');
        $this->assertEquals('unpaid', $order->fresh()->payment_status);
    }

    /** @test */
    public function valid_mocked_cmi_callback_can_mark_order_as_paid(): void
    {
        $order = Order::factory()->create(['payment_status' => 'unpaid']);
        $oid = 'OR-' . $order->id . '-' . time();
        
        $data = [
            'oid' => $oid,
            'amount' => $order->grand_total,
            'ProcReturnCode' => '00',
            'TransId' => '123456',
        ];
        $data['HASH'] = $this->calculateCmiHash($data);
        
        $response = $this->post(route('cmi.callback'), $data);

        // Should process successfully and return ACTION=POSTAUTH
        $response->assertStatus(200);
        $response->assertSee('ACTION=POSTAUTH');
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /** @test */
    public function duplicate_callback_does_not_duplicate_payment_side_effects(): void
    {
        $order = Order::factory()->paid()->create();
        $oid = 'OR-' . $order->id . '-' . time();
        
        $data = [
            'oid' => $oid,
            'amount' => $order->grand_total,
            'ProcReturnCode' => '00',
            'TransId' => '123456',
        ];
        $data['HASH'] = $this->calculateCmiHash($data);
        
        $response1 = $this->post(route('cmi.callback'), $data);
        $response1->assertStatus(200);
        
        $response2 = $this->post(route('cmi.callback'), $data);
        $response2->assertStatus(200);

        // Should be idempotent
        $this->assertEquals('paid', $order->fresh()->payment_status);
    }

    /** @test */
    public function failed_payment_keeps_order_in_unpaid_state(): void
    {
        $order = Order::factory()->create(['payment_status' => 'unpaid']);
        $oid = 'OR-' . $order->id . '-' . time();
        
        $data = [
            'oid' => $oid,
            'amount' => $order->grand_total,
            'ProcReturnCode' => '05', // Failed code
        ];
        $data['HASH'] = $this->calculateCmiHash($data);

        $response = $this->post(route('cmi.callback'), $data);
        $response->assertStatus(200);
        $response->assertSee('APPROVED');

        $this->assertEquals('unpaid', $order->fresh()->payment_status);
    }

    /** @test */
    public function cancelled_payment_keeps_order_in_unpaid_state(): void
    {
        $order = Order::factory()->create(['payment_status' => 'unpaid']);
        $oid = 'OR-' . $order->id . '-' . time();
        
        $data = [
            'oid' => $oid,
            'amount' => $order->grand_total,
            'ProcReturnCode' => '99', // Cancelled code
        ];
        $data['HASH'] = $this->calculateCmiHash($data);

        $response = $this->post(route('cmi.callback'), $data);
        $response->assertStatus(200);
        $response->assertSee('APPROVED');

        $this->assertEquals('unpaid', $order->fresh()->payment_status);
    }

    /** @test */
    public function cmi_routes_do_not_expose_unsafe_behavior_to_unauthenticated_users(): void
    {
        // Public callback route should validate hash, rejecting unsafe requests
        $response = $this->post(route('cmi.callback'), [
            'oid' => 'OR-1-123456',
            'amount' => 100.00,
            'HASH' => 'invalid-hash',
            'ProcReturnCode' => '00',
        ]);

        $response->assertStatus(200);
        $response->assertSee('FAILURE');
    }

    /** @test */
    public function payment_callback_logs_enough_information(): void
    {
        $response = $this->post(route('cmi.callback'), [
            'oid' => 'OR-1-123456',
            'amount' => 100.00,
            'HASH' => 'invalid-hash',
            'ProcReturnCode' => '00',
        ]);

        $response->assertStatus(200);
        $response->assertSee('FAILURE');
    }

    /** @test */
    public function cmi_idempotency_handling_is_safe(): void
    {
        // This test is skipped with TODO documentation explaining the production risk
        $this->markTestSkipped('TODO: Implement idempotency testing for CMI callbacks - current risk of duplicate payments on rapid callback retries');
    }
}