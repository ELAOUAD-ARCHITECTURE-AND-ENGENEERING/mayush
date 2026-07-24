<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $content = (string) $this->getContent();
        if ($content !== '' && strlen($content) > 120000) {
            abort(413, 'Translation payload is too large.');
        }
    }

    public function rules(): array
    {
        return [
            'source_language' => ['required', 'string', 'in:fr'],
            'target_language' => ['required', 'string', 'in:ar'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'fields' => ['required', 'array', 'max:100'],
            'fields.*' => ['nullable'],
        ];
    }
}
