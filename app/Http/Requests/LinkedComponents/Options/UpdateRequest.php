<?php

namespace App\Http\Requests\LinkedComponents\Options;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'component_option_id' => ['prohibited'],
            'linked_component_id' => ['prohibited'],
            'value' => ['nullable']
        ];
    }
}
