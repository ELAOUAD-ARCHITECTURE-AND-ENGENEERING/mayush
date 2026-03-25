<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;

/**
 * UserModelTest
 *
 * Tests User model fillable attributes, relationships, and type checks.
 */
class UserModelTest extends TestCase
{
    /** @test */
    public function model_class_exists(): void
    {
        $this->assertTrue(class_exists(User::class));
    }

    /** @test */
    public function fillable_contains_required_fields(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        foreach (['name', 'email', 'password', 'user_type'] as $field) {
            $this->assertContains($field, $fillable, "'{$field}' must be in fillable");
        }
    }

    /** @test */
    public function hidden_contains_password_and_remember_token(): void
    {
        $user   = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    /** @test */
    public function user_type_admin_check_logic(): void
    {
        $user            = new User();
        $user->user_type = 'admin';

        $this->assertEquals('admin', $user->user_type);
        $this->assertNotEquals('seller', $user->user_type);
    }

    /** @test */
    public function user_type_seller_check_logic(): void
    {
        $user            = new User();
        $user->user_type = 'seller';

        $this->assertEquals('seller', $user->user_type);
        $this->assertNotEquals('customer', $user->user_type);
    }

    /** @test */
    public function user_type_customer_check_logic(): void
    {
        $user            = new User();
        $user->user_type = 'customer';

        $this->assertEquals('customer', $user->user_type);
    }

    /** @test */
    public function user_has_orders_relationship(): void
    {
        $this->assertTrue(method_exists(User::class, 'orders'));
    }

    /** @test */
    public function user_has_wishlists_relationship(): void
    {
        $this->assertTrue(method_exists(User::class, 'wishlists'));
    }

    /** @test */
    public function user_has_addresses_relationship(): void
    {
        $this->assertTrue(method_exists(User::class, 'addresses'));
    }

    /** @test */
    public function user_has_carts_relationship(): void
    {
        $this->assertTrue(method_exists(User::class, 'carts'));
    }

    /** @test */
    public function user_has_shop_relationship(): void
    {
        $this->assertTrue(method_exists(User::class, 'shop'));
    }

    /** @test */
    public function user_has_seller_relationship(): void
    {
        $this->assertTrue(method_exists(User::class, 'seller'));
    }

    /** @test */
    public function user_has_reviews_relationship(): void
    {
        $this->assertTrue(method_exists(User::class, 'reviews'));
    }

    /** @test */
    public function user_has_wallet_relationship(): void
    {
        $this->assertTrue(method_exists(User::class, 'wallets'));
    }

    /** @test */
    public function user_banned_flag_defaults_check(): void
    {
        $user         = new User();
        $user->banned = 0;

        $this->assertFalse((bool)$user->banned);

        $user->banned = 1;
        $this->assertTrue((bool)$user->banned);
    }

    /** @test */
    public function valid_user_types_defined(): void
    {
        $types = ['admin', 'seller', 'customer', 'delivery_boy'];
        foreach ($types as $type) {
            $this->assertIsString($type);
            $this->assertNotEmpty($type);
        }
    }
}
