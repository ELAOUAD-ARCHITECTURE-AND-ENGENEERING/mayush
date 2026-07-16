<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class SellerOnboardingSecurityTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_pending_seller_can_login_to_restricted_dashboard_but_not_products(): void
    {
        $seller = $this->seller(['approval_status' => 'pending', 'registration_approval' => 0]);

        $this->actingAs($seller)
            ->get(route('seller.dashboard'))
            ->assertOk()
            ->assertSee('Complete Registration');

        $this->actingAs($seller)
            ->get(route('seller.products.create'))
            ->assertRedirect(route('seller.onboarding.index'));
    }

    public function test_pending_seller_api_product_operation_is_forbidden(): void
    {
        $seller = $this->seller(['approval_status' => 'pending']);
        Sanctum::actingAs($seller);

        $this->postJson('/api/v2/seller/products/add', [])->assertForbidden()
            ->assertJsonPath('error', 'seller_onboarding_incomplete');
    }

    public function test_seller_profile_updates_cannot_target_another_user(): void
    {
        $seller = $this->seller(['approval_status' => 'pending']);
        $otherSeller = $this->seller(['approval_status' => 'pending']);
        $originalName = $otherSeller->name;

        $this->actingAs($seller)
            ->post(route('seller.profile.update', $otherSeller->id), [
                'name' => 'Unauthorized Change',
                'phone' => '0000000000',
            ])
            ->assertForbidden();

        $this->assertSame($originalName, $otherSeller->fresh()->name);
    }

    public function test_seller_shop_updates_ignore_another_shop_id(): void
    {
        $seller = $this->seller(['approval_status' => 'approved']);
        $otherSeller = $this->seller(['approval_status' => 'approved']);
        $otherShop = $otherSeller->shop->fresh();

        $this->actingAs($seller)
            ->post(route('seller.shop.update'), [
                'shop_id' => $otherShop->id,
                'name' => 'Updated Own Shop',
                'address' => 'Own Shop Address',
            ])
            ->assertRedirect();

        $this->assertSame('Updated Own Shop', $seller->shop->fresh()->name);
        $this->assertSame($otherShop->name, $otherShop->fresh()->name);
    }

    public function test_admin_cannot_approve_incomplete_application(): void
    {
        $seller = $this->seller(['approval_status' => 'under_review']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('sellers.approve', $seller->shop->id))
            ->assertRedirect();

        $this->assertDatabaseHas('shops', [
            'id' => $seller->shop->id,
            'approval_status' => 'under_review',
        ]);
    }

    public function test_documents_are_stored_on_private_disk_and_can_be_versioned(): void
    {
        Storage::fake('seller_documents');
        $seller = $this->seller();

        $this->actingAs($seller)->post(route('seller.onboarding.upload'), [
            'contract' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
            'government_id' => UploadedFile::fake()->create('id.pdf', 100, 'application/pdf'),
            'business_registration' => UploadedFile::fake()->create('business.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->assertDatabaseHas('seller_documents', [
            'shop_id' => $seller->shop->id,
            'document_type' => 'contract',
            'status' => 'pending',
            'version' => 1,
        ]);

        $document = $seller->shop->documents()->where('document_type', 'contract')->firstOrFail();
        Storage::disk('seller_documents')->assertExists($document->file_path);
    }

    public function test_admin_can_approve_only_a_complete_document_package(): void
    {
        $seller = $this->seller(['approval_status' => 'under_review']);
        foreach (['contract', 'government_id', 'business_registration'] as $type) {
            $seller->shop->documents()->create([
                'document_type' => $type,
                'file_path' => $type . '.pdf',
                'original_name' => $type . '.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 100,
                'status' => 'approved',
                'version' => 1,
            ]);
        }
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('sellers.approve', $seller->shop->id))->assertRedirect();

        $this->assertDatabaseHas('shops', [
            'id' => $seller->shop->id,
            'approval_status' => 'approved',
        ]);
    }

    public function test_legacy_verification_flag_cannot_publish_an_unapproved_seller_product(): void
    {
        $seller = $this->seller([
            'approval_status' => 'pending',
            'verification_status' => 1,
        ]);
        $product = Product::factory()->create([
            'added_by' => 'seller',
            'user_id' => $seller->id,
        ]);

        $this->getJson(route('api.products.index'))
            ->assertOk()
            ->assertJsonMissing(['id' => $product->id]);

        $this->assertFalse(Product::publiclyVisible()->whereKey($product->id)->exists());
    }

    public function test_banned_seller_is_hidden_even_when_shop_is_approved(): void
    {
        $seller = $this->seller([
            'approval_status' => 'approved',
            'verification_status' => 1,
        ]);
        $seller->forceFill(['banned' => 1])->save();
        $product = Product::factory()->create([
            'added_by' => 'seller',
            'user_id' => $seller->id,
        ]);

        $this->assertFalse($seller->fresh()->shop->isFullyApproved());
        $this->getJson(route('api.products.index'))
            ->assertOk()
            ->assertJsonMissing(['id' => $product->id]);
    }

    public function test_correction_cycle_preserves_another_rejected_document_and_versions_replacements(): void
    {
        Storage::fake('seller_documents');
        $seller = $this->seller(['approval_status' => 'under_review']);
        foreach (['contract', 'government_id', 'business_registration'] as $type) {
            $seller->shop->documents()->create([
                'document_type' => $type,
                'file_path' => $type . '.pdf',
                'original_name' => $type . '.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 100,
                'status' => 'approved',
                'version' => 1,
            ]);
        }
        $admin = $this->admin();
        $governmentId = $seller->shop->documents()->where('document_type', 'government_id')->firstOrFail();
        $contract = $seller->shop->documents()->where('document_type', 'contract')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('sellers.documents.review', $governmentId), [
                'status' => 'rejected',
                'rejection_reason' => 'The ID is expired.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('shops', [
            'id' => $seller->shop->id,
            'approval_status' => 'rejected',
        ]);

        $this->actingAs($admin)
            ->post(route('sellers.documents.review', $contract), [
                'status' => 'rejected',
                'rejection_reason' => 'The signed contract is incomplete.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('shops', [
            'id' => $seller->shop->id,
            'approval_status' => 'rejected',
        ]);

        $this->flushSession();
        $authenticatedSeller = User::findOrFail($seller->id);
        $this->actingAs($authenticatedSeller);
        $response = $this
            ->post(route('seller.onboarding.upload'), [
                'government_id' => UploadedFile::fake()->create('new-id.pdf', 100, 'application/pdf'),
            ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('shops', [
            'id' => $seller->shop->id,
            'approval_status' => 'under_review',
        ]);
        $this->assertSame(2, $seller->shop->documents()->where('document_type', 'government_id')->count());
        $this->assertDatabaseHas('seller_documents', [
            'shop_id' => $seller->shop->id,
            'document_type' => 'government_id',
            'version' => 2,
            'status' => 'pending',
        ]);
        $this->assertTrue($seller->shop->documents()->where('document_type', 'government_id')->where('version', 1)->where('status', 'rejected')->exists());
        $this->assertTrue($seller->shop->documents()->where('document_type', 'contract')->where('version', 1)->where('status', 'rejected')->exists());
    }

    public function test_optional_rejected_document_can_be_replaced_without_reopening_mandatory_uploads(): void
    {
        Storage::fake('seller_documents');
        $seller = $this->seller(['approval_status' => 'under_review']);
        foreach (['contract', 'government_id', 'business_registration'] as $type) {
            $seller->shop->documents()->create([
                'document_type' => $type,
                'file_path' => $type . '.pdf',
                'original_name' => $type . '.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 100,
                'status' => 'approved',
                'version' => 1,
            ]);
        }
        $certification = $seller->shop->documents()->create([
            'document_type' => 'certification',
            'file_path' => 'certification-v1.pdf',
            'original_name' => 'certification-v1.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'status' => 'pending',
            'version' => 1,
        ]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('sellers.documents.review', $certification), [
                'status' => 'rejected',
                'rejection_reason' => 'Please provide a current certificate.',
            ])
            ->assertRedirect();

        // AuthenticateSession binds one browser session to one password hash;
        // simulate the seller returning in a new session after admin review.
        $this->flushSession();
        $this->actingAs($seller->fresh())
            ->post(route('seller.onboarding.upload'), [
                'certification' => UploadedFile::fake()->create('current-certification.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertSame(2, $seller->shop->documents()->where('document_type', 'certification')->count());
        $this->assertDatabaseHas('seller_documents', [
            'shop_id' => $seller->shop->id,
            'document_type' => 'certification',
            'version' => 2,
            'status' => 'pending',
        ]);
    }

    public function test_document_download_path_traversal_is_rejected(): void
    {
        $seller = $this->seller();
        $document = $seller->shop->documents()->create([
            'document_type' => 'contract',
            'file_path' => '../users/secret.pdf',
            'original_name' => 'contract.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'status' => 'pending',
            'version' => 1,
        ]);

        $this->assertNull($document->safeStoragePath());
    }

    public function test_document_download_requires_the_owner_or_an_administrator(): void
    {
        Storage::fake('seller_documents');
        $owner = $this->seller();
        $otherSeller = $this->seller();
        $admin = $this->admin();
        $document = $owner->shop->documents()->create([
            'document_type' => 'contract',
            'file_path' => 'shop-' . $owner->shop->id . '/contract-v1.pdf',
            'original_name' => 'signed contract.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 100,
            'status' => 'pending',
            'version' => 1,
        ]);
        Storage::disk('seller_documents')->put($document->file_path, '%PDF-test');

        $this->actingAs($owner)
            ->get(route('seller.onboarding.document.download', $document))
            ->assertOk();

        $this->flushSession();
        $this->actingAs($otherSeller)
            ->get(route('seller.onboarding.document.download', $document))
            ->assertForbidden();

        $this->flushSession();
        $this->actingAs($admin)
            ->get(route('sellers.documents.download', $document))
            ->assertOk();
    }

    private function seller(array $shopAttributes = []): User
    {
        $seller = User::factory()->create([
            'user_type' => 'seller',
            'banned' => 0,
        ]);

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
