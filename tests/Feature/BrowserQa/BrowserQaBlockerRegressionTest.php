<?php

namespace Tests\Feature\BrowserQa;

use App\Models\Shop;
use App\Models\User;
use App\Models\Address;
use App\Models\Page;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class BrowserQaBlockerRegressionTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedConfigs();
    }

    public function test_static_assets_do_not_include_public_when_document_root_is_public(): void
    {
        $originalServer = $_SERVER;

        $_SERVER['HTTP_HOST'] = '127.0.0.1:8001';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['DOCUMENT_ROOT'] = public_path();

        $this->assertSame('//127.0.0.1:8001/assets/js/vendors.js', static_asset('assets/js/vendors.js'));

        $_SERVER['DOCUMENT_ROOT'] = base_path();

        $this->assertSame('//127.0.0.1:8001/public/assets/js/vendors.js', static_asset('assets/js/vendors.js'));

        $_SERVER = $originalServer;
    }

    public function test_coupon_helpers_are_safe_when_optional_coupon_tables_are_absent(): void
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $this->actingAs($user);

        $this->assertFalse(ifUserHasWelcomeCouponAndNotUsed());
        $this->assertCount(0, get_coupons());

        $paginated = get_coupons(null, 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);
        $this->assertSame(0, $paginated->total());

        offerUserWelcomeCoupon();

        $this->assertTrue(true);
    }

    public function test_auth_service_uses_safe_layout_when_setting_is_missing(): void
    {
        $this->assertSame('auth.boxed.user_login', app(AuthService::class)->getLoginView('user.login'));

        $this->get(route('user.login'))
            ->assertOk()
            ->assertSee('Login');
    }

    public function test_contact_page_renders_successfully(): void
    {
        $page = new Page();
        $page->forceFill([
            'type' => 'contact_us_page',
            'title' => 'Contact Us',
            'slug' => 'contact-us',
            'content' => json_encode([
                'description' => 'Plain contact page body',
                'address' => 'Test Address',
                'phone' => '123456789',
                'email' => 'test@example.com',
            ]),
        ])->save();

        $this->get('/contact-us')
            ->assertOk()
            ->assertSee('Plain contact page body');
    }

    public function test_customer_dashboard_renders_address_with_missing_location_relations(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        Address::query()->create([
            'user_id' => $customer->id,
            'address' => 'QA Customer Street',
            'city_id' => 999,
            'country_id' => 999,
            'postal_code' => '10000',
            'phone' => '+15550000001',
            'set_default' => 1,
            'set_billing' => 1,
        ]);

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('QA Customer Street');
    }

    public function test_authenticated_customer_visiting_login_redirects_to_existing_homepage(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        $this->actingAs($customer)
            ->get(route('user.login'))
            ->assertRedirect(route('home'));
    }

    public function test_customer_profile_renders_when_no_active_country_exists(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        $this->actingAs($customer)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Manage Profile');
    }

    public function test_checkout_shipping_partial_renders_address_with_missing_city_relation(): void
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        $address = Address::query()->create([
            'user_id' => $customer->id,
            'address' => 'QA Checkout Street',
            'city_id' => 999,
            'country_id' => 999,
            'postal_code' => '10000',
            'phone' => '+15550000002',
            'set_default' => 1,
        ]);

        $this->actingAs($customer);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());

        $this->view('frontend.partials.cart.shipping_info', [
            'address_id' => $address->id,
            'carrier_list' => collect(),
            'shipping_info' => [],
        ])
            ->assertSee('QA Checkout Street');
    }

    public function test_active_payment_methods_are_empty_when_optional_table_is_absent(): void
    {
        $this->assertCount(0, get_activate_payment_methods());
    }

    public function test_seller_dashboard_uses_sqlite_safe_date_grouping(): void
    {
        $seller = User::factory()->seller()->create();
        Shop::factory()->create(['user_id' => $seller->id]);

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertOk();
    }

    public function test_admin_dashboard_uses_sqlite_safe_date_grouping(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
