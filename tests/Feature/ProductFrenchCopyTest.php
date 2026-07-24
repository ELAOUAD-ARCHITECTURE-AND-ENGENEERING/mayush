<?php

namespace Tests\Feature;

use App\Events\ProductRestockedEvent;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductFrenchCopyTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $sellerUser;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([ProductRestockedEvent::class]);

        // Create languages
        Language::updateOrCreate(['code' => 'fr'], ['name' => 'French', 'app_lang_code' => 'fr', 'rtl' => 0, 'status' => 1]);
        Language::updateOrCreate(['code' => 'ar'], ['name' => 'Arabic', 'app_lang_code' => 'ar', 'rtl' => 1, 'status' => 1]);

        // Seed Business Settings
        BusinessSetting::updateOrCreate(
            ['type' => 'business_info'],
            ['value' => json_encode(['gstin' => '123456789'])]
        );

        // Setup all required permissions
        Permission::findOrCreate('add_new_product', 'web');
        Permission::findOrCreate('product_edit', 'web');
        Permission::findOrCreate('show_all_products', 'web');
        Permission::findOrCreate('show_in_house_products', 'web');
        Permission::findOrCreate('show_seller_products', 'web');

        // Create Admin
        $this->adminUser = User::updateOrCreate(
            ['email' => 'admin_copy_test@test.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'user_type' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $this->adminUser->givePermissionTo([
            'add_new_product',
            'product_edit',
            'show_all_products',
            'show_in_house_products',
            'show_seller_products'
        ]);

        // Create Seller & Shop
        $this->sellerUser = User::updateOrCreate(
            ['email' => 'seller_copy_test@test.com'],
            [
                'name' => 'Seller User',
                'password' => bcrypt('password'),
                'user_type' => 'seller',
                'email_verified_at' => now(),
            ]
        );
        Shop::create([
            'user_id' => $this->sellerUser->id,
            'name' => 'Seller Test Shop',
            'slug' => 'seller-test-shop',
            'gst_verification' => 1,
            'approval_status' => 'approved'
        ]);
    }

    /** @test */
    public function admin_product_create_page_renders_language_bar_and_copy_french_button_on_arabic_tab()
    {
        $response = $this->actingAs($this->adminUser)->get(route('products.create', ['lang' => 'ar']));

        $response->assertStatus(200);
        $response->assertSee('Copier le contenu français');
        $response->assertSee('Certains champs de la version arabe contiennent déjà des données.');
        $response->assertSee('Remplir uniquement les champs vides');
        $response->assertSee('Remplacer tout le contenu arabe');
    }

    /** @test */
    public function admin_product_edit_page_renders_copy_french_button_when_lang_is_arabic()
    {
        $category = Category::create(['name' => 'Cat 1', 'slug' => 'cat-1', 'digital' => 0]);

        $product = Product::create([
            'name' => 'Produit Français',
            'added_by' => 'admin',
            'user_id' => $this->adminUser->id,
            'category_id' => $category->id,
            'unit' => 'piece',
            'unit_price' => 100,
            'current_stock' => 10,
            'description' => '<p>Description en français</p>',
            'slug' => 'produit-francais',
            'tags' => json_encode(['decor']),
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('products.admin.edit', ['id' => $product->id, 'lang' => 'ar']));

        $response->assertStatus(200);
        $response->assertSee('Copier le contenu français');
        $response->assertSee('mayush-copy-french-content.js');
    }

    /** @test */
    public function seller_product_edit_page_renders_copy_french_button_when_lang_is_arabic()
    {
        $category = Category::create(['name' => 'Cat Seller', 'slug' => 'cat-seller', 'digital' => 0]);

        $product = Product::create([
            'name' => 'Produit Vendeur FR',
            'added_by' => 'seller',
            'user_id' => $this->sellerUser->id,
            'category_id' => $category->id,
            'unit' => 'kg',
            'unit_price' => 50,
            'current_stock' => 5,
            'description' => '<p>Description du vendeur</p>',
            'slug' => 'produit-vendeur-fr',
            'tags' => json_encode(['decor']),
        ]);

        $response = $this->actingAs($this->sellerUser)->get(route('seller.products.edit', ['id' => $product->id, 'lang' => 'ar']));

        $response->assertStatus(200);
        $response->assertSee('Copier le contenu français');
    }

    /** @test */
    public function product_store_saves_french_and_arabic_translations()
    {
        $category = Category::create(['name' => 'Catégorie Test', 'slug' => 'categorie-test', 'digital' => 0]);

        $data = [
            'name' => 'Nom Produit Copié',
            'unit' => 'piece',
            'description' => '<p>Contenu produit français complet</p>',
            'category_id' => $category->id,
            'category_ids' => [$category->id],
            'unit_price' => 150,
            'min_qty' => 1,
            'current_stock' => 20,
            'tags' => ['decor'],
            'button' => 'publish',
            'lang' => 'ar',
            'translations' => [
                'fr' => [
                    'name' => 'Nom Produit Copié',
                    'unit' => 'piece',
                    'description' => '<p>Contenu produit français complet</p>',
                ],
                'ar' => [
                    'name' => 'Nom Produit Copié (Arabe)',
                    'unit' => 'piece',
                    'description' => '<p>Contenu produit français complet (Arabe)</p>',
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)->post(route('products.store'), $data);

        $response->assertRedirect(route('products.admin'));

        $product = Product::where('name', 'Nom Produit Copié')->first();
        $this->assertNotNull($product);

        $arTranslation = ProductTranslation::where('product_id', $product->id)->where('lang', 'ar')->first();
        $this->assertNotNull($arTranslation);
        $this->assertEquals('Nom Produit Copié (Arabe)', $arTranslation->name);
    }

    /** @test */
    public function product_update_persists_arabic_translation_without_overwriting_french_base()
    {
        $category = Category::create(['name' => 'Category 1', 'slug' => 'cat-1', 'digital' => 0]);

        $product = Product::create([
            'name' => 'Titre Français dOrigine',
            'added_by' => 'admin',
            'user_id' => $this->adminUser->id,
            'category_id' => $category->id,
            'unit' => 'piece',
            'unit_price' => 200,
            'current_stock' => 15,
            'description' => '<p>Description originale en français</p>',
            'slug' => 'titre-francais-d-origine',
            'tags' => json_encode(['decor']),
        ]);

        ProductTranslation::create([
            'product_id' => $product->id,
            'lang' => 'fr',
            'name' => 'Titre Français dOrigine',
            'unit' => 'piece',
            'description' => '<p>Description originale en français</p>'
        ]);

        // Submit update on Arabic tab
        $updateData = [
            'id' => $product->id,
            'lang' => 'ar',
            'name' => 'Titre Arabe Traduit',
            'unit' => 'piece',
            'description' => '<p>Description en arabe copiée puis traduite</p>',
            'category_id' => $category->id,
            'category_ids' => [$category->id],
            'unit_price' => 200,
            'min_qty' => 1,
            'current_stock' => 15,
            'sku' => 'SKU-ORIGINAL',
            'tags' => ['decor'],
            'button' => 'publish',
            'meta_img' => null,
            'thumbnail_img' => null,
        ];

        $response = $this->actingAs($this->adminUser)->from(route('products.admin.edit', ['id' => $product->id, 'lang' => 'ar']))->post(route('products.update', $product->id), $updateData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Base Product should retain base name
        $product->refresh();
        $this->assertEquals('Titre Français dOrigine', $product->name);

        // Arabic translation record should exist with translated text
        $arTranslation = ProductTranslation::where('product_id', $product->id)->where('lang', 'ar')->first();
        $this->assertNotNull($arTranslation);
        $this->assertEquals('Titre Arabe Traduit', $arTranslation->name);
        $this->assertStringContainsString('Description en arabe copiée puis traduite', $arTranslation->description);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_product_create_or_edit()
    {
        $response = $this->get(route('products.create'));
        $response->assertRedirect();
    }
}
