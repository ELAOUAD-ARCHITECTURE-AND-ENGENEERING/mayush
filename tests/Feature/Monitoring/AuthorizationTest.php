<?php

namespace Tests\Feature\Monitoring;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;



    public function test_guest_cannot_access_system_health()
    {
        $response = $this->get(route('admin.system.health'));
        $response->assertRedirect('/login');
    }

    public function test_customer_cannot_access_monitoring()
    {
        $customer = User::factory()->create(['user_type' => 'customer']);

        $response = $this->actingAs($customer)->get(route('admin.system.health'));
        $response->assertStatus(404);
    }

    public function test_admin_can_access_monitoring()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create(['user_type' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.system.health'));
        $response->assertSuccessful();
        $response->assertViewIs('backend.system.health');
    }

    public function test_pulse_and_horizon_gates()
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $staff = User::factory()->create(['user_type' => 'staff']);
        $customer = User::factory()->create(['user_type' => 'customer']);
        $seller = User::factory()->create(['user_type' => 'seller']);

        // Admin and Staff can view Pulse and Horizon
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($admin)->check('viewPulse'));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($admin)->check('viewHorizon'));
        
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($staff)->check('viewPulse'));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($staff)->check('viewHorizon'));

        // Customer and Seller cannot
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($customer)->check('viewPulse'));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($customer)->check('viewHorizon'));

        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($seller)->check('viewPulse'));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($seller)->check('viewHorizon'));

        // Guests cannot
        $this->assertFalse(\Illuminate\Support\Facades\Gate::check('viewPulse'));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::check('viewHorizon'));
    }
}
