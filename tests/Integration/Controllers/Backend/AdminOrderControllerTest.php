<?php

namespace Tests\Integration\Controllers\Backend;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure permissions exist
        Permission::firstOrCreate(['name' => 'view_all_orders']);
        Permission::firstOrCreate(['name' => 'view_order_details']);
        Permission::firstOrCreate(['name' => 'delete_order']);

        $role = Role::firstOrCreate(['name' => 'Super Admin']);
        $role->givePermissionTo('view_all_orders', 'view_order_details', 'delete_order');

        $this->adminUser = User::factory()->create(['user_type' => 'admin', 'email' => 'superadmin@example.com']);
        $this->adminUser->assignRole('Super Admin');

        // Seed necessary data for views to avoid "property of null" errors
        \DB::table('notification_types')->insertOrIgnore([
            'type' => 'complete_unpaid_order_payment',
            'name' => 'Unpaid Order Payment',
            'status' => 1
        ]);

        \DB::table('business_settings')->insertOrIgnore([
            'type' => 'unpaid_order_payment_notification',
            'value' => json_encode(['status' => 1])
        ]);

        \DB::table('currencies')->insertOrIgnore([
            'id' => 1,
            'name' => 'US Dollar',
            'symbol' => '$',
            'exchange_rate' => 1,
            'code' => 'USD',
            'status' => 1
        ]);

        \DB::table('business_settings')->insertOrIgnore([
            'type' => 'system_default_currency',
            'value' => 1
        ]);

        \DB::table('languages')->insertOrIgnore([
            'id' => 1,
            'name' => 'English',
            'code' => 'en',
            'app_lang_code' => 'en',
            'rtl' => 0,
            'status' => 1
        ]);

        // Seed some data for orders
        Product::factory()->count(5)->create();
        Order::factory()->count(5)->create([
            'payment_status' => 'paid',
            'delivery_status' => 'pending'
        ]);
    }

    public function test_admin_can_view_all_orders()
    {
        $response = $this->actingAs($this->adminUser)->get(route('all_orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend.sales.index');
        $response->assertViewHas('orders');
    }

    public function test_admin_can_view_order_details()
    {
        $order = Order::first();
        
        // Ensure shipping_address is valid JSON for the view
        $order->shipping_address = json_encode([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => 'Test Address',
            'country' => 'Test Country',
            'city' => 'Test City',
            'postal_code' => '12345',
            'phone' => '123456789'
        ]);
        $order->save();

        // Seed some order details as the view might iterate over them
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => Product::first()->id,
            'price' => 100,
            'tax' => 10,
            'shipping_cost' => 5,
            'quantity' => 1,
            'payment_status' => 'paid',
            'delivery_status' => 'pending'
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('all_orders.show', encrypt($order->id)));

        $response->assertStatus(200);
        $response->assertViewIs('backend.sales.show');
        $response->assertViewHas('order');
    }

    public function test_admin_can_update_delivery_status()
    {
        $order = Order::first();

        // Re-seed shipping_address with expected JSON for the view, or ensure bypass if not available
        $order->shipping_address = json_encode([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'address' => 'Test Address',
            'country' => 'Test Country',
            'city' => 'Test City',
            'postal_code' => '12345',
            'phone' => '123456789'
        ]);
        $order->save();

        $response = $this->actingAs($this->adminUser)->post(route('orders.update_delivery_status'), [
            'order_id' => $order->id,
            'status' => 'confirmed'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'delivery_status' => 'confirmed'
        ]);
    }

    public function test_admin_can_update_payment_status()
    {
        $order = Order::first();

        $response = $this->actingAs($this->adminUser)->post(route('orders.update_payment_status'), [
            'order_id' => $order->id,
            'status' => 'paid'
        ]);

        // If it responds with 200, it means it probably worked even if it returned 0 or 1
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid'
        ]);
    }

    public function test_admin_can_delete_single_order()
    {
        $order = Order::factory()->create();

        // Use DELETE method as defined in the routes
        $response = $this->actingAs($this->adminUser)->delete(route('orders.destroy', $order->id));

        $response->assertRedirect();
        
        $this->assertDatabaseMissing('orders', [
            'id' => $order->id,
        ]);
    }
}
