<?php

namespace App\Models;

use App\Traits\PreventDemoModeChanges;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BrandsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use PreventDemoModeChanges;

    private const EXPECTED_HEADINGS = [
        'name',
        'logo',
        'meta_title',
        'meta_description',
        'slug',
    ];

    private const REQUIRED_HEADINGS = [
        'name',
        'logo',
        'meta_title',
        'meta_description',
    ];

    public function collection(Collection $rows)
    {
        $this->validateHeadings($rows);

        $importableRows = $rows->reject(function ($row) {
            return $this->isEmptyRow($row);
        });

        if ($importableRows->isEmpty()) {
            $this->failImport(translate('The spreadsheet does not contain any brands.'));
        }

        $normalizedRows = $importableRows->map(function ($row) {
            return $this->normalizeRow($row);
        });

        $seenNames = [];
        foreach ($normalizedRows as $row) {
            $brandName = trim((string) $row['name']);
            $lookupName = Str::lower($brandName);

            if (isset($seenNames[$lookupName])) {
                $this->failImport(translate('Brand name is duplicated inside the uploaded spreadsheet: ') . $brandName);
            }

            $seenNames[$lookupName] = true;
        }

        foreach ($normalizedRows as $row) {
            $brandName = trim((string) $row['name']);

            if ($this->brandNameExists($brandName)) {
                $this->failImport(translate('Brand already exists: ') . $brandName);
            }

            Brand::create([
                'name' => $brandName,
                'logo' => $this->storeLogoReference($row['logo']),
                'slug' => $this->uniqueSlug($row['slug'] ?: $brandName),
                'meta_title' => $row['meta_title'],
                'meta_description' => $row['meta_description'],
            ]);
        }

        flash(translate('Brands imported successfully'))->success();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'distinct',
                function ($attribute, $value, $onFailure) {
                    if ($this->brandNameExists(trim((string) $value))) {
                        $onFailure(translate('Brand already exists: ') . $value);
                    }
                },
            ],
            'logo' => [
                'nullable',
                'string',
                'max:2000',
                function ($attribute, $value, $onFailure) {
                    if (!$this->isValidLogoReference($value)) {
                        $onFailure(translate('Logo must be a valid URL or local asset path.'));
                    }
                },
            ],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'slug' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => translate('Brand name is required.'),
            'name.distinct' => translate('Brand name is duplicated inside the uploaded spreadsheet.'),
            'name.max' => translate('Brand name may not be greater than 50 characters.'),
        ];
    }

    private function validateHeadings(Collection $rows): void
    {
        $firstRow = $rows->first();

        if (!$firstRow) {
            return;
        }

        $headings = array_keys($firstRow->toArray());
        $missing = array_diff(self::REQUIRED_HEADINGS, $headings);

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

    private function isEmptyRow($row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function brandNameExists(string $name): bool
    {
        if ($name === '') {
            return false;
        }

        return Brand::whereRaw('LOWER(name) = ?', [Str::lower($name)])->exists();
    }

    private function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $suffix = 2;

        while (Brand::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function isValidLogoReference($value): bool
    {
        $value = trim((string) $value);

        if ($value === '') {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
        }

        return !str_contains($value, '..')
            && !str_starts_with($value, '/')
            && preg_match('/^[A-Za-z0-9_\-\/.]+$/', $value) === 1;
    }

    private function storeLogoReference($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $upload = new Upload;
        $upload->type = 'image';

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $upload->external_link = $value;
            $upload->file_name = basename(parse_url($value, PHP_URL_PATH) ?: $value);
        } else {
            $upload->file_name = $value;
        }

        $upload->save();

        return $upload->id;
    }
}
