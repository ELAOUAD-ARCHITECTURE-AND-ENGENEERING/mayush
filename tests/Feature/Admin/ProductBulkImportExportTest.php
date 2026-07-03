<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductsExport;
use App\Models\ProductsImport;
use App\Models\ProductStock;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Tests\TestCase;

class ProductBulkImportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_spreadsheet_imports_products_with_images_and_stock(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $category = Category::factory()->create();
        $extraCategory = Category::factory()->create();
        $brand = Brand::factory()->create();

        $this->actingAs($admin);

        Excel::import(new ProductsImport, $this->xlsx([
            $this->productHeadings(),
            [
                'Imported Lamp',
                '<p>Handmade lamp</p>',
                $category->id,
                $extraCategory->id,
                $brand->id,
                'youtube',
                'https://example.com/video',
                'lamp,artisan',
                149.95,
                'pcs',
                'imported-lamp',
                7,
                3,
                'IMP-LAMP-001',
                'Imported Lamp SEO',
                'SEO description',
                'https://cdn.example.com/thumb.jpg',
                'https://cdn.example.com/gallery-a.jpg,https://cdn.example.com/gallery-b.jpg',
            ],
        ]));

        $product = Product::where('name', 'Imported Lamp')->firstOrFail();

        $this->assertSame($brand->id, $product->brand_id);
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame('IMP-LAMP-001', $product->stocks()->first()->sku);
        $this->assertSame(7, $product->stocks()->first()->qty);
        $this->assertDatabaseHas('product_categories', [
            'product_id' => $product->id,
            'category_id' => $extraCategory->id,
        ]);
        $this->assertSame('https://cdn.example.com/thumb.jpg', Upload::find($product->thumbnail_img)->external_link);
        $this->assertCount(2, explode(',', $product->photos));
    }

    public function test_product_import_rejects_missing_required_fields(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $this->actingAs($admin);

        $this->expectException(ExcelValidationException::class);

        Excel::import(new ProductsImport, $this->xlsx([
            $this->productHeadings(),
            [
                '',
                'No name',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'pcs',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
        ]));
    }

    public function test_product_import_rejects_invalid_image_url_without_creating_product(): void
    {
        $admin = User::factory()->create(['user_type' => 'admin']);
        $category = Category::factory()->create();

        $this->actingAs($admin);

        try {
            Excel::import(new ProductsImport, $this->xlsx([
                $this->productHeadings(),
                [
                    'Bad Image Product',
                    'Description',
                    $category->id,
                    '',
                    '',
                    '',
                    '',
                    '',
                    20,
                    'pcs',
                    '',
                    2,
                    '',
                    'BAD-IMG',
                    '',
                    '',
                    'not-a-valid-url',
                    '',
                ],
            ]));
        } catch (ExcelValidationException $e) {
            $this->assertDatabaseMissing('products', ['name' => 'Bad Image Product']);

            return;
        }

        $this->fail('Invalid image URL should have failed validation.');
    }

    public function test_product_export_contains_import_compatible_and_business_critical_fields(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Exported Bag',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'unit_price' => 75,
            'discount' => 10,
            'discount_type' => 'percent',
            'published' => 1,
            'approved' => 1,
            'photos' => '11,12',
            'thumbnail_img' => 11,
            'variations' => json_encode(['Color-Red' => ['price' => 80]]),
        ]);
        ProductStock::factory()->create([
            'product_id' => $product->id,
            'sku' => 'BAG-RED',
            'qty' => 5,
            'price' => 80,
        ]);
        ProductCategory::insert([
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);

        $export = new ProductsExport;
        $row = $export->map($product->fresh()->load(['stocks', 'product_categories']));

        $this->assertSame([
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
            'discount',
            'discount_type',
            'published',
            'approved',
            'variations',
        ], $export->headings());

        $this->assertSame('Exported Bag', $row[0]);
        $this->assertSame($category->id, $row[2]);
        $this->assertSame((string) $category->id, $row[3]);
        $this->assertSame($brand->id, $row[4]);
        $this->assertEquals(75, $row[8]);
        $this->assertSame(5, $row[11]);
        $this->assertSame('BAG-RED', $row[13]);
        $this->assertSame('11,12', $row[17]);
        $this->assertEquals(10, $row[18]);
        $this->assertSame(1, $row[20]);
    }

    private function productHeadings(): array
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

        return UploadedFile::fake()->createWithContent('import.xlsx', $contents);
    }
}
