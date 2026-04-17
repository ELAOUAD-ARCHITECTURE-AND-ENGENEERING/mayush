<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\CombinedOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class RouteHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('cmi.secret_key', 'TEST_SECRET');
    }

    /** @test */
    public function it_throttles_rapid_requests_to_express_buy()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Send 5 requests (below/at limit)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('express.buy', 1));
            // We expect 403 or 302 (validation/session mismatch) but NOT 429
            $this->assertNotEquals(429, $response->getStatusCode(), "Request $i should not be throttled yet");
        }

        // 6th request should be throttled
        $response = $this->post(route('express.buy', 1));
        $this->assertEquals(429, $response->getStatusCode(), '6th request must be throttled');
    }

    /** @test */
    public function it_only_vaults_tokens_if_explicit_opt_in_cache_exists()
    {
        $user = User::factory()->create();
        $order = CombinedOrder::create([
            'user_id' => $user->id,
            'grand_total' => 100
        ]);

        $oid = 'CO-' . $order->id . '-12345';
        $callbackData = [
            'oid' => $oid,
            'amount' => '100',
            'TransId' => 'VAULT_TOKEN_TEST',
            'Response' => 'Approved',
            'ProcReturnCode' => '00',
            'MaskedPan' => '411111XXXXXX1111'
        ];

        // 1. Calculate valid hash for the data
        $callbackData['HASH'] = $this->generateTestHash($callbackData, 'TEST_SECRET');

        // Scenario 1: No Opt-in Cache
        $this->post(route('cmi.callback'), $callbackData);
        
        $this->assertDatabaseMissing('payment_tokens', [
            'user_id' => $user->id
        ]);

        // Scenario 2: With Opt-in Cache
        Cache::put('cmi_save_card_' . $oid, true, 3600);
        
        $this->post(route('cmi.callback'), $callbackData);
        
        $this->assertDatabaseHas('payment_tokens', [
            'user_id' => $user->id,
            'gateway' => 'cmi'
        ]);
    }

    /**
     * Helper to generate CMI Hash (Mirrors CmiController logic)
     */
    private function generateTestHash($data, $storeKey)
    {
        $postParams = array_keys($data);
        natcasesort($postParams);

        $hashval = "";
        foreach ($postParams as $param){
            $paramValue = $data[$param];
            $paramValue = html_entity_decode(preg_replace("/\n$/","", $paramValue), ENT_QUOTES, 'UTF-8');
            $paramValue = preg_replace('/document./i', 'document.', $paramValue);
            $escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", $paramValue)); 
            
            $lowerParam = strtolower($param);
            if($lowerParam != "hash" && $lowerParam != "encoding" ) {
                $hashval = $hashval . $escapedParamValue . "|";
            }
        }

        $escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
        $hashval = $hashval . $escapedStoreKey;

        $calculatedHashValue = hash('sha512', $hashval);
        return base64_encode(pack('H*', $calculatedHashValue));
    }
}
