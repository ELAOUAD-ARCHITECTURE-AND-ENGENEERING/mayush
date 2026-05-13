<?php

namespace Tests\Feature\V109;

use App\Models\PaymentInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class PaymentInformationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_customer_can_create_and_default_first_payment_information(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)->post(route('payment-informations.store'), [
            'payment_type' => 'bank_transfer',
            'bank_name' => 'other_bank',
            'other_bank_name' => 'Test Bank',
            'account_name' => 'Customer Account',
            'account_number' => '123456',
            'routing_number' => '999',
        ])->assertRedirect();

        $this->assertDatabaseHas('payment_informations', [
            'user_id' => $customer->id,
            'bank_name' => 'Test Bank',
            'set_default' => 1,
        ]);
    }

    public function test_customer_cannot_update_or_delete_another_customer_payment_information(): void
    {
        $owner = User::factory()->customer()->create();
        $other = User::factory()->customer()->create();
        $paymentInformation = PaymentInformation::create([
            'user_id' => $owner->id,
            'payment_type' => 'bank_transfer',
            'bank_name' => 'Owner Bank',
            'account_name' => 'Owner',
            'account_number' => '111',
        ]);

        $this->actingAs($other)->post(route('payment-informations.update'), [
            'payment_information_id' => $paymentInformation->id,
            'payment_type' => 'bank_transfer',
            'bank_name' => 'other_bank',
            'other_bank_name' => 'Other Bank',
            'account_name' => 'Other',
            'account_number' => '222',
        ])->assertNotFound();

        $this->actingAs($other)->delete(route('payment-informations.destroy', $paymentInformation->id))
            ->assertNotFound();

        $this->assertDatabaseHas('payment_informations', [
            'id' => $paymentInformation->id,
            'bank_name' => 'Owner Bank',
        ]);
    }

    public function test_payment_information_destroy_is_not_get(): void
    {
        $route = Route::getRoutes()->getByName('payment-informations.destroy');

        $this->assertNotNull($route);
        $this->assertSame(['DELETE'], $route->methods());
    }
}
