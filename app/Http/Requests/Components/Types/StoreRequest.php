<?php

namespace App\Http\Requests\Components\Types;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'is_plural' => ['boolean'],
            'has_option' => ['boolean'],
            'has_default' => ['boolean'],
            'max_count' => ['integer'],
            'attributes' => ['string']
        ];
    }
}
