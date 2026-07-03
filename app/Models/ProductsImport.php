<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Traits\PreventDemoModeChanges;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use PreventDemoModeChanges;

    private const EXPECTED_HEADINGS = [
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

    public function collection(Collection $rows)
    {
        $canImport = true;
        $user = Auth::user();

        if (!$user) {
            throw new \RuntimeException(translate('You must be logged in to import products.'));
        }

        $this->validateHeadings($rows);
        $importableRows = $rows->reject(function ($row) {
            return $this->isEmptyRow($row);
        });

        if ($importableRows->isEmpty()) {
            $this->failImport(translate('The spreadsheet does not contain any products.'));
        }

        $admin = User::where('user_type', 'admin')->first();
        if (!$admin) {
            throw new \RuntimeException(translate('No admin user exists for assigning imported products.'));
        }

        if ($user->user_type == 'seller' && addon_is_activated('seller_subscription')) {
            if (($importableRows->count() + $user->products()->count()) > $user->shop->product_upload_limit
                || $user->shop->package_invalid_at == null
                || Carbon::now()->diffInDays(Carbon::parse($user->shop->package_invalid_at), false) < 0
            ) {
                $canImport = false;
                flash(translate('Please upgrade your package.'))->warning();
            }
        }

        if ($canImport) {
            foreach ($importableRows as $row) {
                $row = $this->normalizeRow($row);
                $approved = 1;
                if ($user->user_type == 'seller' && get_setting('product_approve_by_admin') == 1) {
                    $approved = 0;
                }

                $slug = $this->normalizeSlug($row['slug'] ?: $row['name']);
                $thumbnail = $this->downloadThumbnail($row['thumbnail_img']);
                $gallery = $this->downloadGalleryImages($row['photos']);

                $product = $this->productForSyncKey($row['sku'], $user);
                $productPayload = [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'category_id' => $row['category_id'],
                    'brand_id' => $row['brand_id'],
                    'video_provider' => $row['video_provider'],
                    'video_link' => $row['video_link'],
                    'tags' => $row['tags'],
                    'unit_price' => $row['unit_price'],
                    'unit' => $row['unit'],
                    'meta_title' => $row['meta_title'],
                    'meta_description' => $row['meta_description'],
                    'est_shipping_days' => $row['est_shipping_days'],
                    'colors' => json_encode(array()),
                    'choice_options' => json_encode(array()),
                    'variations' => json_encode(array()),
                    'thumbnail_img' => $thumbnail,
                    'photos' => $gallery,
                ];

                if ($product) {
                    $product->update($productPayload);
                } else {
                    $product = Product::create($productPayload + [
                        'added_by' => $user->user_type == 'seller' ? 'seller' : 'admin',
                        'user_id' => $user->user_type == 'seller' ? $user->id : $admin->id,
                        'approved' => $approved,
                        'slug' => $slug . '-' . Str::random(5),
                    ]);
                }

                $stock = $this->stockForSyncKey($product, $row['sku'])
                    ?: ProductStock::firstOrNew([
                        'product_id' => $product->id,
                        'variant' => '',
                    ]);
                $stock->fill([
                    'product_id' => $product->id,
                    'variant' => $stock->variant ?: '',
                    'qty' => $row['current_stock'],
                    'price' => $row['unit_price'],
                    'sku' => $row['sku'],
                ]);
                $stock->save();

                foreach (array_unique($this->parseCommaSeparatedValues($row['multi_categories'])) as $category_id) {
                    ProductCategory::insertOrIgnore([
                        "product_id" => $product->id,
                        "category_id" => (int) $category_id,
                    ]);
                }
            }

            flash(translate('Products imported successfully'))->success();
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'multi_categories' => [
                'nullable',
                function ($attribute, $value, $onFailure) {
                    $this->validateCommaSeparatedIds($attribute, $value, $onFailure, Category::class, 'categories');
                },
            ],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'video_provider' => ['nullable', 'string', Rule::in(['youtube', 'dailymotion', 'vimeo'])],
            'video_link' => ['nullable', 'url', 'max:1000'],
            'tags' => ['nullable', 'string', 'max:1000'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'slug' => ['nullable', 'string', 'max:255'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'est_shipping_days' => ['nullable', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255', 'distinct'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'thumbnail_img' => ['nullable', 'url', 'max:2000'],
            'photos' => [
                'nullable',
                function ($attribute, $value, $onFailure) {
                    $this->validateCommaSeparatedUrls($attribute, $value, $onFailure);
                },
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => translate('Product name is required.'),
            'category_id.required' => translate('Category ID is required.'),
            'category_id.exists' => translate('Category ID does not exist.'),
            'brand_id.integer' => translate('Brand ID must be a whole number.'),
            'brand_id.exists' => translate('Brand ID does not exist.'),
            'video_provider.in' => translate('Video provider must be youtube, dailymotion, or vimeo.'),
            'video_link.url' => translate('Video link must be a valid URL.'),
            'unit_price.required' => translate('Unit price is required.'),
            'unit_price.numeric' => translate('Unit price must be numeric.'),
            'unit_price.min' => translate('Unit price cannot be negative.'),
            'unit.required' => translate('Unit is required.'),
            'current_stock.required' => translate('Current stock is required.'),
            'current_stock.integer' => translate('Current stock must be a whole number.'),
            'current_stock.min' => translate('Current stock cannot be negative.'),
            'est_shipping_days.integer' => translate('Estimated shipping days must be a whole number.'),
            'est_shipping_days.min' => translate('Estimated shipping days cannot be negative.'),
            'sku.unique' => translate('SKU already exists.'),
            'sku.distinct' => translate('SKU is duplicated inside the uploaded spreadsheet.'),
            'thumbnail_img.url' => translate('Thumbnail image must be a valid URL.'),
            'photos.url' => translate('Photo URLs must be valid URLs.'),
        ];
    }

    public function downloadThumbnail($url)
    {
        $url = trim((string) $url);

        if (!$url) {
            return null;
        }

        try {
            $upload = new Upload;
            $upload->external_link = $url;
            $upload->type = 'image';
            $upload->save();

            return $upload->id;
        } catch (\Exception $e) {
        }
        return null;
    }

    public function downloadGalleryImages($urls)
    {
        if (!$urls) {
            return null;
        }

        $data = array();
        foreach ($this->parseCommaSeparatedValues($urls) as $url) {
            $uploadId = $this->downloadThumbnail($url);
            if (!$uploadId) {
                continue;
            }
            $data[] = $uploadId;
        }

        return empty($data) ? null : implode(',', $data);
    }

    private function isEmptyRow($row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function validateHeadings(Collection $rows): void
    {
        $firstRow = $rows->first();

        if (!$firstRow) {
            return;
        }

        $headings = array_keys($firstRow->toArray());
        $missing = array_diff(self::EXPECTED_HEADINGS, $headings);

        if (!empty($missing)) {
            $this->failImport(translate('Missing required spreadsheet columns: ') . implode(', ', $missing));
        }
    }

    private function failImport(string $message): void
    {
        Validator::make([], [])->after(function ($validator) use ($message) {
            $validator->errors()->add('bulk_file', $message);
        })->validate();
    }

    private function normalizeRow($row): array
    {
        $normalized = [];

        foreach (self::EXPECTED_HEADINGS as $heading) {
            $value = $row[$heading] ?? null;
            $normalized[$heading] = is_string($value) ? trim($value) : $value;
        }

        return $normalized;
    }

    private function normalizeSlug($value): string
    {
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($value)));

        return trim($slug, '-') ?: Str::random(8);
    }

    private function parseCommaSeparatedValues($value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $value)), function ($item) {
            return $item !== '';
        }));
    }

    private function productForSyncKey($sku, User $user): ?Product
    {
        $sku = trim((string) $sku);

        if ($sku === '') {
            return null;
        }

        $stock = ProductStock::where('sku', $sku)
            ->whereHas('product', function ($query) use ($user) {
                if ($user->user_type === 'seller') {
                    $query->where('user_id', $user->id);
                }
            })
            ->with('product')
            ->first();

        return $stock?->product;
    }

    private function stockForSyncKey(Product $product, $sku): ?ProductStock
    {
        $sku = trim((string) $sku);

        if ($sku === '') {
            return null;
        }

        return ProductStock::where('product_id', $product->id)->where('sku', $sku)->first();
    }

    private function validateCommaSeparatedIds($attribute, $value, $onFailure, string $modelClass, string $label): void
    {
        foreach ($this->parseCommaSeparatedValues($value) as $id) {
            if (!ctype_digit($id) || !$modelClass::whereKey((int) $id)->exists()) {
                $onFailure(translate('The ') . $attribute . translate(' field contains an invalid ') . $label . translate(' ID: ') . $id);
            }
        }
    }

    private function validateCommaSeparatedUrls($attribute, $value, $onFailure): void
    {
        foreach ($this->parseCommaSeparatedValues($value) as $url) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $onFailure(translate('The ') . $attribute . translate(' field contains an invalid URL: ') . $url);
            }
        }
    }
}
