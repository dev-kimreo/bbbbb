<?php

namespace App\Http\Requests\LinkedComponents;

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
            'linked_component_group_id' => ['prohibited'],
            'component_id' => ['prohibited'],
            'name' => ['required_without_all:sort', 'string'],
            'sort' => ['required_without_all:name', 'integer', 'min:1'],
        ];
    }
}
