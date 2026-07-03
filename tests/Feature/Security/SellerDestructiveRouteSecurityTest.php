<?php

namespace Tests\Feature\Security;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class SellerDestructiveRouteSecurityTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedConfigs();
    }

    public function test_seller_product_destroy_rejects_get_and_is_owner_scoped(): void
    {
        $seller = $this->approvedSeller();
        $otherSeller = $this->approvedSeller();

        $ownProduct = Product::factory()->create(['user_id' => $seller->id, 'added_by' => 'seller']);
        $otherProduct = Product::factory()->create(['user_id' => $otherSeller->id, 'added_by' => 'seller']);

        $this->actingAs($seller)
            ->get(route('seller.products.destroy', $ownProduct->id))
            ->assertNotFound();

        $this->actingAs($seller)
            ->delete(route('seller.products.destroy', $otherProduct->id))
            ->assertForbidden();

        $this->actingAs($seller)
            ->delete(route('seller.products.destroy', $ownProduct->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('products', ['id' => $ownProduct->id]);
        $this->assertDatabaseHas('products', ['id' => $otherProduct->id]);
    }

    public function test_seller_digital_product_destroy_rejects_get_and_is_owner_scoped(): void
    {
        $seller = $this->approvedSeller();
        $otherSeller = $this->approvedSeller();

        $ownProduct = Product::factory()->digital()->create(['user_id' => $seller->id, 'added_by' => 'seller']);
        $otherProduct = Product::factory()->digital()->create(['user_id' => $otherSeller->id, 'added_by' => 'seller']);

        $this->actingAs($seller)
            ->get(route('seller.digitalproducts.destroy', $ownProduct->id))
            ->assertNotFound();

        $this->actingAs($seller)
            ->delete(route('seller.digitalproducts.destroy', $otherProduct->id))
            ->assertForbidden();

        $this->actingAs($seller)
            ->delete(route('seller.digitalproducts.destroy', $ownProduct->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('products', ['id' => $ownProduct->id]);
        $this->assertDatabaseHas('products', ['id' => $otherProduct->id]);
    }

    public function test_seller_upload_destroy_rejects_get_and_is_owner_scoped(): void
    {
        $seller = $this->approvedSeller();
        $otherSeller = $this->approvedSeller();

        $ownUpload = Upload::create([
            'user_id' => $seller->id,
            'file_original_name' => 'seller-file',
            'file_name' => 'uploads/test/seller-file.txt',
            'extension' => 'txt',
            'type' => 'document',
            'file_size' => 10,
        ]);
        $otherUpload = Upload::create([
            'user_id' => $otherSeller->id,
            'file_original_name' => 'other-file',
            'file_name' => 'uploads/test/other-file.txt',
            'extension' => 'txt',
            'type' => 'document',
            'file_size' => 10,
        ]);

        $this->actingAs($seller)
            ->get(route('seller.my_uploads.destroy', $ownUpload->id))
            ->assertNotFound();

        $this->actingAs($seller)
            ->delete(route('seller.my_uploads.destroy', $otherUpload->id))
            ->assertForbidden();

        $this->actingAs($seller)
            ->delete(route('seller.my_uploads.destroy', $ownUpload->id))
            ->assertRedirect();

        $this->assertSoftDeleted('uploads', ['id' => $ownUpload->id]);
        $this->assertDatabaseHas('uploads', ['id' => $otherUpload->id, 'deleted_at' => null]);
    }

    public function test_seller_custom_label_delete_no_longer_accepts_get(): void
    {
        $seller = $this->approvedSeller();

        $this->actingAs($seller)
            ->get(route('seller.custom_label.delete', 123))
            ->assertNotFound();
    }

    private function approvedSeller(): User
    {
        $seller = User::factory()->seller()->create();

        Shop::factory()->create([
            'user_id' => $seller->id,
            'registration_approval' => 1,
            'approval_status' => 'approved',
        ]);

        return $seller;
    }
}
