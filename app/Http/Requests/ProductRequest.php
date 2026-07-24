<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Attribute;

class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('category_id') && $this->route('product')?->category_id) {
            $this->merge([
                'category_id' => $this->route('product')->category_id,
            ]);
        }

        if (!$this->filled('category_id')) {
            return;
        }

        $categoryIds = collect((array) $this->input('category_ids', []))
            ->filter(fn ($categoryId) => $categoryId !== null && $categoryId !== '')
            ->map(fn ($categoryId) => (string) $categoryId);

        $mainCategoryId = (string) $this->input('category_id');
        if (!$categoryIds->contains($mainCategoryId)) {
            $categoryIds->push($mainCategoryId);
        }

        $this->merge([
            'category_ids' => $categoryIds->values()->all(),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        $rules['name']          = 'required|max:255';
        $rules['category_ids']  = 'required';
        $rules['category_id']   = ['required', Rule::in($this->category_ids)];
        $rules['unit']         = ['sometimes', 'required', 'regex:/^[\p{L}\s]+$/u'];
        $rules['min_qty']      = 'sometimes|required|numeric';
        $rules['unit_price']    = 'sometimes|required|numeric|gt:0';
        if ($this->get('discount_type') == 'amount') {
            $rules['discount'] = 'nullable|numeric|lt:unit_price';
        } else {
            $rules['discount'] = 'nullable|numeric|lt:100';
        }
        $rules['current_stock'] = 'sometimes|required|numeric';
        $rules['starting_bid']  = 'sometimes|required|numeric|min:1';
        $rules['auction_date_range']  = 'sometimes|required';

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $removedVariants = collect($this->input('removed_sku_variants', []))
                ->map(fn ($variant) => $this->normalizeDimensionVariant((string) $variant))
                ->filter()
                ->all();

            foreach ($this->dimensionAttributeIds() as $attributeId) {
                $field = 'choice_options_' . $attributeId;
                $values = collect($this->input($field, []))
                    ->filter(fn ($value) => !in_array($this->normalizeDimensionVariant((string) $value), $removedVariants, true))
                    ->values();

                if ($values->isEmpty()) {
                    $validator->errors()->add($field, translate('Add at least one dimension variant.'));
                    continue;
                }

                $seen_variants = [];
                $seen_dimensions = [];
                foreach ($values as $value) {
                    $dimension = trim((string) $value);
                    $normalized = $this->normalizeDimensionVariant($dimension);

                    if ($dimension === '') {
                        $validator->errors()->add($field, translate('Dimension variant values cannot be empty.'));
                        continue;
                    }

                    if (!$this->isValidDimensionVariant($dimension)) {
                        $validator->errors()->add(
                            $field,
                            translate('Use a dimension such as 10x20x30 cm, 1-100cm, or +1000cm.')
                        );
                        continue;
                    }

                    $suffix = str_replace('.', '_', preg_replace('/\s+/', '', $dimension));
                    $prices = $this->input('price_' . $suffix);
                    $lengths = $this->input('length_' . $suffix);
                    $widths = $this->input('width_' . $suffix);
                    $heights = $this->input('height_' . $suffix);
                    $units = $this->input('unit_' . $suffix);

                    if (!isset($seen_variants[$normalized])) {
                        $seen_variants[$normalized] = [];
                    }
                    $occurrenceIndex = count($seen_variants[$normalized]);

                    $price = is_array($prices) ? ($prices[$occurrenceIndex] ?? null) : $prices;
                    $length = is_array($lengths) ? ($lengths[$occurrenceIndex] ?? 0) : ($lengths ?? 0);
                    $width = is_array($widths) ? ($widths[$occurrenceIndex] ?? 0) : ($widths ?? 0);
                    $height = is_array($heights) ? ($heights[$occurrenceIndex] ?? 0) : ($heights ?? 0);
                    $unit = strtolower((string) (is_array($units) ? ($units[$occurrenceIndex] ?? 'cm') : ($units ?? 'cm')));

                    $dimensionKey = $this->dimensionSelectionKey($length, $width, $height, $unit);
                    if ($dimensionKey !== null && isset($seen_dimensions[$dimensionKey])) {
                        $validator->errors()->add($field, translate('Exact dimension choices must be unique.'));
                        break 2;
                    }
                    if ($dimensionKey !== null) {
                        $seen_dimensions[$dimensionKey] = true;
                    }

                    // Check if we have already seen a variant with the exact same price and L, W, H
                    foreach ($seen_variants[$normalized] as $existing) {
                        if ((float)$existing['price'] === (float)$price &&
                            (float)$existing['length'] === (float)$length &&
                            (float)$existing['width'] === (float)$width &&
                            (float)$existing['height'] === (float)$height) {
                            $validator->errors()->add($field, translate('Duplicate variants with the same price and dimensions are not allowed.'));
                            break 2;
                        }
                    }

                    $seen_variants[$normalized][] = [
                        'price' => $price,
                        'length' => $length,
                        'width' => $width,
                        'height' => $height,
                    ];
                }
            }
        });
    }

    private function dimensionAttributeIds(): array
    {
        $choiceIds = collect($this->input('choice_no', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($choiceIds->isEmpty()) {
            return [];
        }

        return Attribute::whereIn('id', $choiceIds)
            ->get(['id', 'name'])
            ->filter(fn ($attribute) => (int) $attribute->id === 35 || strtolower((string) $attribute->name) === 'dimension')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function normalizeDimensionVariant(string $value): string
    {
        return strtolower((string) preg_replace('/\s+/', '', trim($value)));
    }

    private function isValidDimensionVariant(string $value): bool
    {
        $number = '\d+(?:\.\d+)?';
        $unit = '(?:cm|mm|m|in|inch|inches)';
        $value = trim($value);

        return preg_match('/^' . $number . '\s*x\s*' . $number . '\s*x\s*' . $number . '\s*' . $unit . '$/i', $value) === 1
            || preg_match('/^' . $number . '\s*-\s*' . $number . '\s*' . $unit . '$/i', $value) === 1
            || preg_match('/^\+?' . $number . '\s*' . $unit . '$/i', $value) === 1;
    }

    private function dimensionSelectionKey($length, $width, $height, string $unit): ?string
    {
        if ((float) $length <= 0 && (float) $width <= 0 && (float) $height <= 0) {
            return null;
        }

        return implode(':', [
            number_format((float) $length, 4, '.', ''),
            number_format((float) $width, 4, '.', ''),
            number_format((float) $height, 4, '.', ''),
            $unit ?: 'cm',
        ]);
    }

    /**
     * Get the validation messages of rules that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [
            'name.required'             => translate('Product name is required'),
            'category_ids.required'     => translate('Product category is required'),
            'category_id.required'      => translate('Main Category is required'),
            'category_id.in'            => translate('Main Category must be within selected categories'),
            'unit.required'             => translate('Product unit is required'),
            'unit.regex' => 'The unit may only contain letters and spaces.',
            'min_qty.required'          => translate('Minimum purchase quantity is required'),
            'min_qty.numeric'           => translate('Minimum purchase must be numeric'),
            'unit_price.gt'             => translate('The unit price must be greater than 0'),
            'unit_price.required'       => translate('Unit price is required'),
            'unit_price.numeric'        => translate('Unit price must be numeric'),
            'discount.required'         => translate('Discount is required'),
            'discount.numeric'          => translate('Discount must be numeric'),
            'discount.lt'               => translate('Discount should be less than unit price'),
            'current_stock.required'    => translate('Current stock is required'),
            'current_stock.numeric'     => translate('Current stock must be numeric'),
            'starting_bid.required'     => translate('Starting Bid is required'),
            'starting_bid.numeric'      => translate('Starting Bid must be numeric'),
            'starting_bid.required'     => translate('Minimum Starting Bid is 1'),
            'auction_date_range.required' => translate('Auction Date Range is required'),
        ];

        return $messages;
    }

}
