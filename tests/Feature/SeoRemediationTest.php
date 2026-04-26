<?php

namespace Tests\Feature;

use App\Services\SeoService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeoRemediationTest extends TestCase
{
    public function test_sitemap_route_serves_public_xml_file(): void
    {
        $path = public_path('sitemap.xml');
        $backup = file_exists($path) ? file_get_contents($path) : null;

        file_put_contents($path, "\n<?xml version=\"1.0\" encoding=\"UTF-8\"?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"><url><loc>https://mayushdesign.com/</loc></url></urlset>");

        try {
            $response = $this->get('/sitemap.xml');

            $response->assertOk();
            $this->assertStringContainsString('application/xml', $response->headers->get('content-type'));
            $this->assertStringStartsWith('<?xml', $response->getContent());
            $this->assertStringContainsString('https://mayushdesign.com/', $response->getContent());
        } finally {
            if ($backup === null) {
                @unlink($path);
            } else {
                file_put_contents($path, $backup);
            }
        }
    }

    public function test_robots_route_serves_public_txt_file(): void
    {
        $path = public_path('robots.txt');
        $backup = file_exists($path) ? file_get_contents($path) : null;

        file_put_contents($path, "User-agent: *\nAllow: /\nSitemap: https://mayushdesign.com/sitemap.xml\n");

        try {
            $response = $this->get('/robots.txt');

            $response->assertOk();
            $this->assertStringContainsString('text/plain', $response->headers->get('content-type'));
            $this->assertStringContainsString('Sitemap: https://mayushdesign.com/sitemap.xml', $response->getContent());
        } finally {
            if ($backup === null) {
                @unlink($path);
            } else {
                file_put_contents($path, $backup);
            }
        }
    }

    public function test_public_robots_declares_ai_crawlers_and_content_signals(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        foreach ([
            'Googlebot',
            'Bingbot',
            'GPTBot',
            'OAI-SearchBot',
            'ChatGPT-User',
            'ClaudeBot',
            'Claude-Web',
            'anthropic-ai',
            'PerplexityBot',
            'Google-Extended',
            'CCBot',
        ] as $bot) {
            $this->assertStringContainsString("User-agent: {$bot}", $robots);
        }

        $this->assertStringContainsString('Content-Signal: ai-train=yes, search=yes, ai-input=yes', $robots);
        $this->assertStringContainsString('Sitemap: https://mayushdesign.com/sitemap.xml', $robots);
    }

    public function test_agent_discovery_endpoints_are_truthful_and_reachable(): void
    {
        $catalog = $this->get('/.well-known/api-catalog');
        $catalog->assertOk();
        $this->assertStringContainsString('application/linkset+json', $catalog->headers->get('content-type'));
        $catalog->assertJsonPath('linkset.0.service-desc.0.href', url('/openapi.json'));

        $openApi = $this->get('/openapi.json');
        $openApi->assertOk();
        $this->assertStringContainsString('application/openapi+json', $openApi->headers->get('content-type'));
        $openApi->assertJsonPath('openapi', '3.0.0');
        $openApi->assertJsonPath('components.securitySchemes.systemKey.name', 'System-Key');
        $openApi->assertJsonPath('paths./promotions.get.security.0.systemKey', []);

        $skills = $this->get('/.well-known/agent-skills/index.json');
        $skills->assertOk();
        $skills->assertJsonStructure(['$schema', 'skills']);
        $this->assertNotEmpty($skills->json('skills'));
    }

    public function test_public_html_responses_include_agent_discovery_link_headers(): void
    {
        $response = $this->get('/docs/api');

        $response->assertOk();
        $link = $response->headers->get('Link');

        $this->assertStringContainsString('</.well-known/api-catalog>; rel="api-catalog"', $link);
        $this->assertStringContainsString('</openapi.json>; rel="service-desc"', $link);
        $this->assertStringContainsString('</.well-known/agent-skills/index.json>', $link);
    }

    public function test_markdown_for_agents_negotiates_public_html_pages(): void
    {
        $response = $this->get('/docs/api', ['Accept' => 'text/markdown']);

        $response->assertOk();
        $this->assertStringContainsString('text/markdown', $response->headers->get('content-type'));
        $this->assertTrue((int) $response->headers->get('X-Markdown-Tokens') > 0);
        $this->assertStringContainsString('# Mayush API Documentation', $response->getContent());
        $this->assertStringContainsString('Canonical:', $response->getContent());
    }

    public function test_json_ld_helper_outputs_valid_json_for_html_content(): void
    {
        $json = SeoService::jsonLd([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => 'Mayush "SEO" Page',
            'description' => SeoService::cleanText('<p>Furniture &amp; decor with "quoted" content.</p>'),
        ]);

        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertSame('WebPage', $decoded['@type']);
        $this->assertSame('Furniture & decor with "quoted" content.', $decoded['description']);
    }

    public function test_sitemap_command_is_registered_on_the_schedule(): void
    {
        Artisan::call('schedule:list');

        $this->assertStringContainsString('app:generate-sitemap', Artisan::output());
    }

    public function test_generated_sitemap_rejects_localhost_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'http://localhost']);

        $status = Artisan::call('app:generate-sitemap');

        $this->assertSame(1, $status);
        $this->assertStringContainsString('local APP_URL', Artisan::output());
    }
}
