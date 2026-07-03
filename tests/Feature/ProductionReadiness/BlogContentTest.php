<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;
use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use Tests\Traits\SeedsAppConfigs;

class BlogContentTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    /** @test */
    public function blog_listing_loads(): void
    {
        $response = $this->get(route('blog'));

        $response->assertStatus(200);
    }

    /** @test */
    public function blog_detail_loads(): void
    {
        $blog = Blog::factory()->create([
            'status' => 1,
            'published_at' => now()
        ]);

        $response = $this->get(route('blog.details', $blog->slug));

        while (ob_get_level() > 1) {
            ob_end_clean();
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function blog_email_capture_endpoint_validates_input(): void
    {
        $response = $this->post('/blog/subscribe', [
            'email' => 'invalid-email'
        ]);

        // Should validate email format
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function invalid_email_is_rejected(): void
    {
        $response = $this->post('/blog/subscribe', [
            'email' => 'not-an-email'
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function blog_lead_capture_does_not_call_external_services_during_tests(): void
    {
        Http::fake();

        $response = $this->post('/blog/subscribe', [
            'email' => 'test@example.com'
        ]);

        // Should not make external calls
        Http::assertNothingSent();
    }
}