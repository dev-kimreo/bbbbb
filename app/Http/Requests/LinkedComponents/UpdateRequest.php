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
            'linked_component_group_id' => ['required_without_all:name,sort,display_on_pc,display_on_mobile', 'integer', 'exists:App\Models\LinkedComponents\LinkedComponentGroup,id'],
            'component_id' => ['prohibited'],
            'name' => ['required_without_all:linked_component_group_id,sort,display_on_pc,display_on_mobile', 'string'],
            'sort' => ['required_without_all:linked_component_group_id,name,display_on_pc,display_on_mobile', 'integer', 'min:1'],
            'display_on_pc' => ['required_without_all:linked_component_group_id,name,sort,display_on_mobile', 'boolean'],
            'display_on_mobile' => ['required_without_all:linked_component_group_id,name,sort,display_on_pc', 'boolean'],
        ];
    }
}
