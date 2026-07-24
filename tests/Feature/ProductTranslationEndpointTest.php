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
            'services.azure_translator.key' => 'test-key',
            'services.azure_translator.region' => null,
            'services.azure_translator.endpoint' => 'https://translator.test',
            'services.azure_translator.api_version' => '3.0',
        ]);
    }

    public function test_authenticated_product_manager_can_translate_without_saving_a_product(): void
    {
        Http::fake(function (HttpRequest $request) {
            return Http::response([
                ['translations' => [['text' => 'مكتب']]],
                ['translations' => [['text' => '<p>وصف</p>']]],
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
            ->assertJsonPath('fields.name', 'مكتب')
            ->assertJsonPath('fields.description', '<p>وصف</p>')
            ->assertJsonPath('fields.unit_price', '100');
        $this->assertDatabaseCount('products', 0);
        Http::assertSent(fn (HttpRequest $request) => $request->hasHeader('Ocp-Apim-Subscription-Key', 'test-key'));
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
