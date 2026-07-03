<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\BrandsImport;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Tests\TestCase;

class BrandBulkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_brands_import_with_url_and_local_logo_references(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        Excel::import(new BrandsImport, $this->xlsx([
            $this->brandHeadings(),
            ['Atlas Studio', 'https://cdn.example.com/atlas.png', 'Atlas SEO', 'Atlas description', 'atlas-studio'],
            ['Local Loom', 'uploads/all/local-loom.png', 'Local SEO', 'Local description', 'local-loom'],
        ]));

        $atlas = Brand::where('name', 'Atlas Studio')->firstOrFail();
        $local = Brand::where('name', 'Local Loom')->firstOrFail();

        $this->assertSame('atlas-studio', $atlas->slug);
        $this->assertSame('https://cdn.example.com/atlas.png', Upload::find($atlas->logo)->external_link);
        $this->assertSame('uploads/all/local-loom.png', Upload::find($local->logo)->file_name);
        $this->assertNull(Upload::find($local->logo)->external_link);
    }

    public function test_brand_import_rejects_existing_duplicate_name(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        Brand::factory()->create(['name' => 'Atlas Studio']);

        $this->expectException(ExcelValidationException::class);

        Excel::import(new BrandsImport, $this->xlsx([
            $this->brandHeadings(),
            ['Atlas Studio', '', '', '', 'atlas-studio-2'],
        ]));
    }

    public function test_brand_import_rejects_invalid_logo_reference(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        try {
            Excel::import(new BrandsImport, $this->xlsx([
                $this->brandHeadings(),
                ['Unsafe Logo', '../secret/logo.png', '', '', 'unsafe-logo'],
            ]));
        } catch (ExcelValidationException $e) {
            $this->assertDatabaseMissing('brands', ['name' => 'Unsafe Logo']);

            return;
        }

        $this->fail('Unsafe logo reference should have failed validation.');
    }

    public function test_brand_import_rejects_duplicate_names_inside_same_spreadsheet(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        Excel::import(new BrandsImport, $this->xlsx([
            $this->brandHeadings(),
            ['Atlas Studio', '', '', '', 'atlas-studio'],
            ['atlas studio', '', '', '', 'atlas-studio-alt'],
        ]));
    }

    public function test_brand_import_generates_unique_slug_when_slug_collides(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'admin']));
        Brand::factory()->create(['name' => 'Existing A', 'slug' => 'shared-slug']);

        Excel::import(new BrandsImport, $this->xlsx([
            $this->brandHeadings(),
            ['Existing B', '', '', '', 'shared-slug'],
        ]));

        $this->assertDatabaseHas('brands', [
            'name' => 'Existing B',
            'slug' => 'shared-slug-2',
        ]);
    }

    private function brandHeadings(): array
    {
        return [
            'name',
            'logo',
            'meta_title',
            'meta_description',
            'slug',
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

        return UploadedFile::fake()->createWithContent('brands.xlsx', $contents);
    }
}
