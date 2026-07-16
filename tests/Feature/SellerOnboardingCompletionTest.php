<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class SellerOnboardingCompletionTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_legacy_admin_mutation_is_a_410_compatibility_response(): void
    {
        $seller = $this->seller(['registration_approval' => 0]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('sellers.approved'), ['id' => $seller->shop->id, 'status' => 1])
            ->assertStatus(410)
            ->assertJsonPath('error', 'seller_onboarding_legacy_flow_disabled');

        $this->assertDatabaseHas('shops', [
            'id' => $seller->shop->id,
            'approval_status' => 'pending',
            'registration_approval' => 0,
        ]);
    }

    public function test_legacy_api_verification_endpoints_are_410_shims(): void
    {
        $seller = $this->seller();
        Sanctum::actingAs($seller);

        $this->getJson('/api/v2/seller/shop-verify-form')
            ->assertStatus(410)
            ->assertJsonPath('error', 'seller_onboarding_legacy_flow_disabled');

        $this->postJson('/api/v2/seller/shop-verify-info-store', [])
            ->assertStatus(410)
            ->assertJsonPath('error', 'seller_onboarding_legacy_flow_disabled');
    }

    public function test_internal_sellers_are_visible_only_when_approved_and_vendor_mode_is_disabled(): void
    {
        BusinessSetting::updateOrCreate(['type' => 'vendor_system_activation'], ['value' => '0']);

        $internal = $this->seller(['approval_status' => 'approved'], ['is_intern' => 1]);
        $external = $this->seller(['approval_status' => 'approved'], ['is_intern' => 0]);
        $internalProduct = Product::factory()->create([
            'added_by' => 'seller',
            'user_id' => $internal->id,
            'approved' => 1,
            'published' => 1,
        ]);
        $externalProduct = Product::factory()->create([
            'added_by' => 'seller',
            'user_id' => $external->id,
            'approved' => 1,
            'published' => 1,
        ]);

        $this->assertTrue(Product::publiclyVisible()->whereKey($internalProduct->id)->exists());
        $this->assertFalse(Product::publiclyVisible()->whereKey($externalProduct->id)->exists());
        $this->assertTrue(Shop::publiclyVisible()->whereKey($internal->shop->id)->exists());
        $this->assertFalse(Shop::publiclyVisible()->whereKey($external->shop->id)->exists());
    }

    public function test_legacy_migration_is_private_pending_and_idempotent(): void
    {
        Storage::fake('seller_documents');
        $seller = $this->seller();
        $directory = public_path('uploads/verification_form');
        File::ensureDirectoryExists($directory);
        $legacyPath = $directory . DIRECTORY_SEPARATOR . 'signed-contract.pdf';
        File::put($legacyPath, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF\n");

        try {
            $seller->shop->verification_info = json_encode([
                [
                    'type' => 'file',
                    'label' => 'Signed Contract',
                    'value' => 'uploads/verification_form/signed-contract.pdf',
                ],
            ]);
            $seller->shop->save();

            Artisan::call('seller-onboarding:migrate-legacy-verification', ['--dry-run' => true]);
            $this->assertDatabaseCount('seller_documents', 0);

            Artisan::call('seller-onboarding:migrate-legacy-verification', ['--commit' => true]);
            $this->assertDatabaseCount('seller_documents', 1);
            $document = $seller->shop->documents()->firstOrFail();
            $this->assertSame('contract', $document->document_type);
            $this->assertSame('pending', $document->status);
            $this->assertNotSame($document->legacy_file_path, $document->file_path);
            Storage::disk('seller_documents')->assertExists($document->file_path);

            Artisan::call('seller-onboarding:migrate-legacy-verification', ['--commit' => true]);
            $this->assertDatabaseCount('seller_documents', 1);
        } finally {
            File::delete($legacyPath);
        }
    }

    private function seller(array $shopAttributes = [], array $userAttributes = []): User
    {
        $seller = User::factory()->create(array_merge([
            'user_type' => 'seller',
            'banned' => 0,
        ], $userAttributes));

        Shop::factory()->create(array_merge([
            'user_id' => $seller->id,
            'approval_status' => 'pending',
            'registration_approval' => 0,
        ], $shopAttributes));

        return $seller->fresh();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));

        return $admin->fresh();
    }
}
