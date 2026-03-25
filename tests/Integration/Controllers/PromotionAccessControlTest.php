<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\CustomerProduct;
use App\Models\Promotion;
use App\Models\Language;
use App\Models\BusinessSetting;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

/**
 * PromotionAccessControlTest
 *
 * Verifies RBAC enforcement and product ownership checks across
 * the promotion and classified product features.
 */
class PromotionAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $sellerA;
    protected $sellerB;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Create missing tables dynamically for SQLite test DB
        // since they were added via raw SQL and lack migrations.
        // We drop them first to ensure RefreshDatabase doesn't leave them in a stale state.
        \Illuminate\Support\Facades\Schema::dropIfExists('customer_products');
        \Illuminate\Support\Facades\Schema::create('customer_products', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('added_by')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->string('photos')->nullable();
            $table->string('thumbnail_img')->nullable();
            $table->string('conditon')->nullable();
            $table->string('location')->nullable();
            $table->string('video_provider')->nullable();
            $table->string('video_link')->nullable();
            $table->string('unit')->nullable();
            $table->string('tags')->nullable();
            $table->longText('description')->nullable();
            $table->double('unit_price')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_img')->nullable();
            $table->string('pdf')->nullable();
            $table->string('slug')->nullable();
            $table->integer('status')->default(0);
            $table->integer('published')->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::dropIfExists('customer_product_translations');
        \Illuminate\Support\Facades\Schema::create('customer_product_translations', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->integer('customer_product_id');
            $table->string('name')->nullable();
            $table->string('unit')->nullable();
            $table->longText('description')->nullable();
            $table->string('lang')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::dropIfExists('top_banners');
        \Illuminate\Support\Facades\Schema::create('top_banners', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('status')->default(1);
        });

        \Illuminate\Support\Facades\Schema::dropIfExists('element_types');
        \Illuminate\Support\Facades\Schema::create('element_types', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::dropIfExists('element_styles');
        \Illuminate\Support\Facades\Schema::create('element_styles', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->integer('element_type_id');
            $table->string('name')->nullable();
            $table->string('value')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::dropIfExists('elements');
        \Illuminate\Support\Facades\Schema::create('elements', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Language::updateOrCreate(['code' => 'en'], ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]);
        BusinessSetting::updateOrCreate(['type' => 'classified_product'], ['value' => '1']);
        
        $et = \Illuminate\Support\Facades\DB::table('element_types')->insertGetId([
            'name' => 'Header 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        BusinessSetting::updateOrCreate(['type' => 'header_element'], ['value' => $et]);

        $this->admin = User::factory()->admin()->create();
        $this->sellerA = User::factory()->seller()->create(['remaining_uploads' => 10]);
        $this->sellerB = User::factory()->seller()->create(['remaining_uploads' => 10]);
        $this->customer = User::factory()->customer()->create();

        $shopA = new Shop();
        $shopA->user_id = $this->sellerA->id;
        $shopA->name = 'Shop A';
        $shopA->save();

        $shopB = new Shop();
        $shopB->user_id = $this->sellerB->id;
        $shopB->name = 'Shop B';
        $shopB->save();
    }

    /**
     * Helper to create a CustomerProduct for a given user.
     */
    private function createProductFor(User $user): CustomerProduct
    {
        $product = new CustomerProduct();
        $product->name = 'Test Product ' . Str::random(5);
        $product->user_id = $user->id;
        $product->added_by = 'seller';
        $product->status = 1;
        $product->published = 1;
        $product->unit_price = 100;
        $product->slug = 'test-' . Str::random(8);
        $product->save();

        return $product;
    }

    // ─── Customer Restrictions ──────────────────────────────────────────────

    /** @test */
    public function customer_cannot_access_classified_products_index(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer_products.index'));
        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function customer_cannot_store_classified_product(): void
    {
        $this->withoutExceptionHandling();
        try {
            dump("Logged in user: " . (auth()->user() ? auth()->user()->id : 'NULL'));
            $this->actingAs($this->customer)->post(route('customer_products.store'), [
                'name' => 'Hacked Product',
            ]);
            $this->fail('Expected a 403 Forbidden exception but none was thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode(), "Expected 403 but got " . $e->getStatusCode());
            $this->assertEquals('Access Denied. Customers cannot store classified products.', $e->getMessage());
        }
    }

    /** @test */
    public function customer_cannot_promote_classified_product(): void
    {
        $product = $this->createProductFor($this->sellerA);

        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->customer)->post(route('customer_products.promote'), [
                'product_id' => $product->id,
                'tier' => 'standard',
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
            ]);
            $this->fail('Expected a 403 Forbidden exception but none was thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    // ─── Seller Ownership Checks ────────────────────────────────────────────

    /** @test */
    public function seller_cannot_edit_another_sellers_product(): void
    {
        $product = $this->createProductFor($this->sellerB);

        $this->withoutExceptionHandling();
        try {
            $response = $this->actingAs($this->sellerA)->get(route('customer_products.edit', $product->id));
            dump("Response status: " . $response->getStatusCode());
            if ($response->getStatusCode() == 302) {
                dump("Redirect URL: " . $response->headers->get('Location'));
            }
            $this->fail('Expected a 403 Forbidden exception but none was thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /** @test */
    public function seller_cannot_update_another_sellers_product(): void
    {
        $product = $this->createProductFor($this->sellerB);

        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->sellerA)->post(route('customer_products.update', $product->id), [
                'name' => 'Hijacked Name',
                'lang' => 'en',
            ]);
            $this->fail('Expected a 403 Forbidden exception but none was thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /** @test */
    public function seller_cannot_delete_another_sellers_product(): void
    {
        $product = $this->createProductFor($this->sellerB);

        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->sellerA)->get(route('customer_products.destroy', $product->id));
            $this->fail('Expected a 403 Forbidden exception but none was thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    /** @test */
    public function seller_cannot_promote_another_sellers_product(): void
    {
        $product = $this->createProductFor($this->sellerB);

        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->sellerA)->post(route('customer_products.promote'), [
                'product_id' => $product->id,
                'tier' => 'standard',
                'start_date' => now()->addDay()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
            ]);
            $this->fail('Expected a 403 Forbidden exception but none was thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    // ─── Admin Promotions Guard ─────────────────────────────────────────────

    /** @test */
    public function non_admin_cannot_access_promotions_index(): void
    {
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->sellerA)->get(route('promotions.index'));
            $this->fail('Expected a 403 Forbidden exception but none was thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    /** @test */
    public function customer_cannot_access_promotions_index(): void
    {
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($this->customer)->get(route('promotions.index'));
            $this->fail('Expected a 403 Forbidden exception but none was thrown.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(404, $e->getStatusCode());
        }
    }

    // ─── Expire Promotions Command ──────────────────────────────────────────

    /** @test */
    public function expire_command_sets_past_promotions_to_expired(): void
    {
        $product = $this->createProductFor($this->sellerA);

        Promotion::create([
            'user_id' => $this->sellerA->id,
            'product_id' => $product->id,
            'tier' => 'standard',
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDay(),
            'status' => 'approved',
        ]);

        $this->artisan('promotions:expire')->assertSuccessful();

        $this->assertDatabaseHas('promotions', [
            'product_id' => $product->id,
            'status' => 'expired',
        ]);
    }
}
