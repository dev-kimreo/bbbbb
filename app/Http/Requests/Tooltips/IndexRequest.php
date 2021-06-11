<?php

namespace App\Http\Requests\Tooltips;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
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
        return [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|between:1,50',
            'has_lang' => 'array',
            'visible' => 'nullable|boolean',
            'type' => 'nullable|string',
            'title' => 'nullable|string',
            'code' => 'nullable|string|regex:/^[A-Z]{2}_[0-9]+$/',
            'user_name' => 'nullable|string',
        ];
    }
}
