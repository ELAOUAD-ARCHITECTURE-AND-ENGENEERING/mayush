<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogSubscribeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'placement' => ['required', 'string', Rule::in([
                'listing_inline',
                'mid_article',
                'sidebar',
                'post_read',
            ])],
            'blog_id' => ['nullable', 'integer', 'exists:blogs,id'],
            'website' => ['nullable', 'prohibited'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => translate('Please enter your email address.'),
            'email.email' => translate('Please enter a valid email address.'),
            'placement.required' => translate('The subscription placement is required.'),
            'placement.in' => translate('The subscription placement is invalid.'),
            'blog_id.exists' => translate('The selected article could not be found.'),
            'website.prohibited' => translate('Something went wrong. Please try again.'),
        ];
    }
}
