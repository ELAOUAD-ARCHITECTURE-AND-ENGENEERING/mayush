<?php

namespace Tests\Feature\Checkout;

use App\Models\Address;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\City;
use App\Models\Country;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckoutAccountPromptFlowTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;
    private City $city;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );

        foreach ([
            'site_name' => 'MayushTest',
            'language' => 'en',
            'google_recaptcha' => '0',
            'cloudflare_turnstile' => '0',
            'color_scheme' => 'default',
            'guest_checkout_activation' => '0',
            'email_verification' => '0',
            'customer_registration_verify' => '0',
            'has_state' => '0',
            'billing_address_required' => '0',
            'shipping_type' => 'flat_rate',
            'minimum_order_amount_check' => '0',
        ] as $type => $value) {
            BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
        }

        $this->admin = User::factory()->admin()->create(['email' => 'admin@example.test']);

        $this->country = Country::create([
            'name' => 'Morocco',
            'code' => 'MA',
            'status' => 1,
            'zone_id' => 1,
        ]);

        $this->city = City::create([
            'name' => 'Casablanca',
            'country_id' => $this->country->id,
            'status' => 1,
            'cost' => 0,
        ]);
    }

    public function test_guest_checkout_page_is_reachable_and_uses_checkout_modal_instead_of_login_redirect(): void
    {
        $this->createGuestCart('guest-checkout-page');

        $response = $this->withSession(['temp_user_id' => 'guest-checkout-page'])->get(route('checkout.shipping_info'));

        $response->assertOk();
        $response->assertSee('checkout-account-modal');
        $response->assertDontSee('guest_shipping_info');
    }

    public function test_email_registration_creates_address_logs_in_and_keeps_checkout_ajax_flow(): void
    {
        $this->createGuestCart('guest-email');

        $response = $this
            ->withSession(['temp_user_id' => 'guest-email'])
            ->postJson(route('checkout.account_address'), $this->accountPayload([
                'verification_method' => 'email',
                'email' => 'buyer@example.test',
            ]));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('authenticated', true)
            ->assertJsonStructure(['shipping_info', 'delivery_info', 'cart_summary']);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'buyer@example.test', 'user_type' => 'customer']);
        $this->assertDatabaseHas('addresses', ['address' => 'Checkout Street 1']);
        $this->assertDatabaseHas('carts', ['temp_user_id' => null, 'address_id' => $response->json('address_id')]);
    }

    public function test_phone_registration_path_creates_and_logs_in_customer(): void
    {
        $this->createGuestCart('guest-phone');

        $response = $this
            ->withSession(['temp_user_id' => 'guest-phone'])
            ->postJson(route('checkout.account_address'), $this->accountPayload([
                'verification_method' => 'phone',
                'email' => null,
                'account_country_code' => '212',
                'account_phone' => '612345678',
            ]));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('authenticated', true);

        $this->assertAuthenticated();
        $this->assertSame('+212612345678', auth()->user()->phone);
    }

    public function test_existing_customer_can_login_from_checkout_without_redirect(): void
    {
        $this->createGuestCart('guest-login');
        $customer = User::factory()->customer()->create([
            'email' => 'existing@example.test',
            'password' => Hash::make('Password1'),
        ]);

        Address::factory()->create([
            'user_id' => $customer->id,
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'set_default' => 1,
        ]);

        $response = $this
            ->withSession(['temp_user_id' => 'guest-login'])
            ->postJson(route('checkout.account_address'), [
                'action' => 'login',
                'login_method' => 'email',
                'login_email' => 'existing@example.test',
                'login_password' => 'Password1',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('authenticated', true);

        $this->assertAuthenticatedAs($customer);
        $this->assertDatabaseHas('carts', ['user_id' => $customer->id, 'temp_user_id' => null]);
    }

    public function test_logged_in_customer_without_address_sees_address_only_path(): void
    {
        $customer = User::factory()->customer()->create();
        Cart::factory()->create([
            'user_id' => $customer->id,
            'product_id' => $this->createProduct()->id,
            'status' => 1,
        ]);

        $response = $this
            ->actingAs($customer)
            ->postJson(route('checkout.account_address'), $this->addressPayload(['action' => 'address']));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('authenticated', true);

        $this->assertAuthenticatedAs($customer);
        $this->assertDatabaseHas('addresses', ['user_id' => $customer->id, 'address' => 'Checkout Street 1']);
    }

    public function test_pre_checkout_product_and_cart_templates_no_longer_gate_add_to_cart_with_login_modal(): void
    {
        $productDetails = file_get_contents(resource_path('views/frontend/product_details/details.blade.php'));
        $cartDetails = file_get_contents(resource_path('views/frontend/partials/cart_details.blade.php'));

        $this->assertStringContainsString('onclick="buyNow()"', $productDetails);
        $this->assertStringContainsString('onclick="addToCart()"', $productDetails);
        $this->assertStringNotContainsString('onclick="showLoginModal()"', $cartDetails);
    }

    private function createGuestCart(string $tempUserId): void
    {
        $product = $this->createProduct();

        Cart::factory()->create([
            'user_id' => null,
            'temp_user_id' => $tempUserId,
            'product_id' => $product->id,
            'status' => 1,
            'quantity' => 1,
        ]);
    }

    private function createProduct(): Product
    {
        $product = Product::factory()->create([
            'added_by' => 'admin',
            'user_id' => $this->admin->id,
            'unit_price' => 100,
            'min_qty' => 1,
        ]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'variant' => '',
            'price' => 100,
            'qty' => 10,
        ]);

        return $product;
    }

    private function accountPayload(array $overrides = []): array
    {
        return array_merge($this->addressPayload(), [
            'action' => 'register',
            'name' => 'Checkout Buyer',
            'verification_method' => 'email',
            'email' => 'checkout@example.test',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ], $overrides);
    }

    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'delivery_address' => 'Checkout Street 1',
            'delivery_country_id' => $this->country->id,
            'delivery_city_id' => $this->city->id,
            'delivery_area_id' => null,
            'delivery_postal_code' => '20000',
            'delivery_country_code' => '212',
            'delivery_phone' => '600000000',
        ], $overrides);
    }
}
