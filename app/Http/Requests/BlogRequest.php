<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('blog') ?: $this->route('id'); // Get ID if we are updating

        return [
            'category_id' => ['required', 'integer', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('blogs', 'slug')->ignore($id)],
            'short_description' => ['required', 'string'],
            'workflow_action' => ['nullable', \Illuminate\Validation\Rule::in(['draft', 'submit', 'publish', 'preview'])],
            'published_at' => ['nullable', 'date'],
            'banner' => ['nullable', 'integer'], // upload id
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_img' => ['nullable', 'integer'], // upload id
            'content_blocks' => ['nullable', 'string'], // JSON string, we will validate inside the service
        ];
    }
}
