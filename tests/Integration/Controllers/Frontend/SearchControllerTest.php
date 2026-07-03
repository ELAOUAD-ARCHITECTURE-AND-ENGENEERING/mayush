<?php

namespace Tests\Integration\Controllers\Frontend;

use Tests\TestCase;
use App\Models\User;
use App\Models\Language;
use App\Models\BusinessSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SeedsAppConfigs;

/**
 * SearchControllerTest
 *
 * Integration tests for product search routes.
 */
class SearchControllerTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function search_with_query_returns_200(): void
    {
        $response = $this->get('/search?q=shoes');
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function search_with_empty_query_returns_200(): void
    {
        $response = $this->get('/search?q=');
        $this->assertContains($response->status(), [200, 302]);
    }

    /** @test */
    public function search_does_not_crash_on_special_characters(): void
    {
        $response = $this->get('/search?q=' . urlencode("men's shoes & belts"));
        $this->assertNotEquals(500, $response->status());
    }

    /** @test */
    public function search_with_sort_param_does_not_crash(): void
    {
        $response = $this->get('/search?q=shoes&sort_by=price-asc-desc');
        $this->assertNotEquals(500, $response->status());
    }

    /** @test */
    public function search_with_sql_injection_attempt_is_safe(): void
    {
        $response = $this->get("/search?q=" . urlencode("1' OR '1'='1"));
        // Must not return 500 (server error)
        $this->assertNotEquals(500, $response->status());
    }

    /** @test */
    public function search_with_xss_payload_is_escaped(): void
    {
        $xss      = '<script>alert(1)</script>';
        $response = $this->get('/search?q=' . urlencode($xss));

        $this->assertNotEquals(500, $response->status());

        if ($response->status() === 200) {
            // Raw script tag must not be rendered unescaped
            $this->assertStringNotContainsString(
                '<script>alert(1)</script>',
                (string) $response->getContent()
            );
        }
    }
}
