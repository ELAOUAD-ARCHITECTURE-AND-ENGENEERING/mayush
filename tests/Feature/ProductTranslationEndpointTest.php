<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Tests\TestCase;

class ProductTranslationEndpointTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('add_new_product', 'web');
        Permission::findOrCreate('product_edit', 'web');
        $this->admin = User::factory()->create([
            'user_type' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->admin->givePermissionTo(['add_new_product', 'product_edit']);
        config([
            'services.openrouter.key' => 'test-key',
            'services.openrouter.model' => 'openrouter/free',
            'services.openrouter.api_base' => 'https://openrouter.ai/api/v1',
            'services.openrouter.site_url' => 'https://mayushdesign.com',
            'services.openrouter.app_name' => 'MAYUSH',
            'services.openrouter.max_retries' => 0,
        ]);
    }

    public function test_authenticated_product_manager_can_translate_without_saving_a_product(): void
    {
        Http::fake(function () {
            $translated = json_encode(['fields' => [
                'name' => 'Translated name',
                'description' => '[MAYUSH_HTML_0]Translated description[MAYUSH_HTML_1]',
                'unit_price' => '[MAYUSH_VALUE_2]',
            ]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return Http::response([
                'model' => 'openrouter/free',
                'choices' => [['message' => ['content' => $translated]]],
            ], 200);
        });

        $response = $this->actingAs($this->admin)->postJson(route('products.translate_to_arabic'), [
            'source_language' => 'fr',
            'target_language' => 'ar',
            'fields' => [
                'name' => 'Bureau',
                'description' => '<p>Description</p>',
                'unit_price' => '100',
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('fields.name', 'Translated name')
            ->assertJsonPath('fields.description', '<p>Translated description</p>')
            ->assertJsonPath('fields.unit_price', '100');
        $this->assertDatabaseCount('products', 0);
        Http::assertSent(fn (HttpRequest $request) => $request->url() === 'https://openrouter.ai/api/v1/chat/completions' && $request->hasHeader('Authorization', 'Bearer test-key'));
    }

    public function test_unauthorized_user_cannot_use_the_admin_translation_endpoint(): void
    {
        $user = User::factory()->create(['user_type' => 'customer']);

        $response = $this->actingAs($user)->postJson(route('products.translate_to_arabic'), [
            'source_language' => 'fr',
            'target_language' => 'ar',
            'fields' => ['name' => 'Bureau'],
        ]);

        $this->assertContains($response->status(), [302, 403, 404]);
    }
}
