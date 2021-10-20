<?php

namespace App\Http\Requests\LinkedComponents;

use Illuminate\Foundation\Http\FormRequest;

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
            'linked_component_group_id' => ['required', 'integer', 'exists:App\Models\LinkedComponents\LinkedComponentGroup,id'],
            'component_id' => ['required', 'integer', 'exists:App\Models\Components\Component,id'],
            'name' => ['sometimes', 'string'],
            'sort' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
