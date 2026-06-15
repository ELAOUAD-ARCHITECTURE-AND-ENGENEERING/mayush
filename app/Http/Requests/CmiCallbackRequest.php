<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CmiCallbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // The callback endpoint is public (called by CMI servers)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'oid' => 'required|string|max:100',
            'amount' => 'required|numeric',
            'HASH' => 'required|string',
            'ProcReturnCode' => 'sometimes|string',
            'TransId' => 'sometimes|string',
            // Allow any other parameters to pass through as they are needed for hash validation
        ];
    }
}
