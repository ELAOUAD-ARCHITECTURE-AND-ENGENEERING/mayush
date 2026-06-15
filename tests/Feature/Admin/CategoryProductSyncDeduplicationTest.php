<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductsImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CategoryProductSyncDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_import_uses_sku_as_sync_key_and_updates_existing_product(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $firstCategory = Category::factory()->create();
        $secondCategory = Category::factory()->create();

        $this->actingAs($admin);

        Excel::import(new ProductsImport, $this->xlsx([
            $this->headings(),
            $this->row('SYNC-SKU-1', 'Original Synced Product', $firstCategory->id, $secondCategory->id, 25, 3),
        ]));

        Excel::import(new ProductsImport, $this->xlsx([
            $this->headings(),
            $this->row('SYNC-SKU-1', 'Updated Synced Product', $secondCategory->id, $secondCategory->id, 39, 8),
        ]));

        $this->assertSame(1, Product::whereHas('stocks', fn ($query) => $query->where('sku', 'SYNC-SKU-1'))->count());

        $product = Product::whereHas('stocks', fn ($query) => $query->where('sku', 'SYNC-SKU-1'))->firstOrFail();

        $this->assertSame('Updated Synced Product', $product->name);
        $this->assertSame($secondCategory->id, $product->category_id);
        $this->assertEquals(39, $product->unit_price);
        $this->assertSame(8, $product->stocks()->where('sku', 'SYNC-SKU-1')->first()->qty);
    }

    public function test_product_category_sync_uses_product_and_category_as_deduplication_key(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $primaryCategory = Category::factory()->create();
        $extraCategory = Category::factory()->create();

        $this->actingAs($admin);

        Excel::import(new ProductsImport, $this->xlsx([
            $this->headings(),
            $this->row('SYNC-SKU-2', 'Synced Product Categories', $primaryCategory->id, $extraCategory->id, 15, 2),
        ]));

        Excel::import(new ProductsImport, $this->xlsx([
            $this->headings(),
            $this->row('SYNC-SKU-2', 'Synced Product Categories Updated', $primaryCategory->id, $extraCategory->id, 16, 4),
        ]));

        $product = Product::whereHas('stocks', fn ($query) => $query->where('sku', 'SYNC-SKU-2'))->firstOrFail();

        $this->assertSame(1, ProductCategory::where([
            'product_id' => $product->id,
            'category_id' => $extraCategory->id,
        ])->count());
    }

    public function test_product_category_unique_key_makes_insert_or_ignore_idempotent(): void
    {
        $product = Product::factory()->create();
        $category = Category::factory()->create();

        ProductCategory::insertOrIgnore([
            'product_id' => $product->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ProductCategory::insertOrIgnore([
            'product_id' => $product->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(1, ProductCategory::where([
            'product_id' => $product->id,
            'category_id' => $category->id,
        ])->count());
    }

    private function headings(): array
    {
        return [
            'name',
            'description',
            'category_id',
            'multi_categories',
            'brand_id',
            'video_provider',
            'video_link',
            'tags',
            'unit_price',
            'unit',
            'slug',
            'current_stock',
            'est_shipping_days',
            'sku',
            'meta_title',
            'meta_description',
            'thumbnail_img',
            'photos',
        ];
    }

    private function row(string $sku, string $name, int $categoryId, int $extraCategoryId, int $price, int $stock): array
    {
        return [
            $name,
            'Synced description',
            $categoryId,
            (string) $extraCategoryId,
            '',
            '',
            '',
            'sync',
            $price,
            'pcs',
            'synced-product',
            $stock,
            '',
            $sku,
            '',
            '',
            '',
            '',
        ];
    }

    private function xlsx(array $rows): UploadedFile
    {
        $contents = Excel::raw(new class($rows) implements FromArray {
            public function __construct(private array $rows)
            {
            }

            public function array(): array
            {
                return $this->rows;
            }
        }, \Maatwebsite\Excel\Excel::XLSX);

        return UploadedFile::fake()->createWithContent('sync.xlsx', $contents);
    }
}
