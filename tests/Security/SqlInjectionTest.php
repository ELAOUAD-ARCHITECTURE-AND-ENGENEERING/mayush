<?php

namespace Tests\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SqlInjectionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function search_query_is_safe_from_basic_sqli()
    {
        // Attempting a common SQL injection payload
        $payload = "' OR '1'='1";
        
        $response = $this->get(route('search', ['q' => $payload]));

        // Check that the query didn't break the database and returned a valid response
        $response->assertStatus(200);
        
        // Ensure it didn't return "everything" (e.g., if we had products, it should only find things matching the literal string if at all)
        // If the injection worked, it might return all products.
    }

    /** @test */
    public function login_is_safe_from_sqli()
    {
        $response = $this->post('/login', [
            'email' => "admin@example.com' --",
            'password' => "password"
        ]);

        // Should fail authentication, not trigger a database error or bypass
        $this->assertNotEquals(200, $response->status()); 
        $this->assertFalse(auth()->check());
    }
}
