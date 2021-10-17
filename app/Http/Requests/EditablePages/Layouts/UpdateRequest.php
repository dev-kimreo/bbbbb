<?php

namespace App\Http\Requests\EditablePages\Layouts;

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
            'editable_page_id' => ['prohibited'],
            'header_component_group_id' => ['required_without_all:content_component_group_id,footer_component_group_id', 'integer', 'exists:App\Models\LinkedComponents\LinkedComponentGroup,id'],
            'content_component_group_id' => ['required_without_all:header_component_group_id,footer_component_group_id', 'integer', 'exists:App\Models\LinkedComponents\LinkedComponentGroup,id'],
            'footer_component_group_id' => ['required_without_all:content_component_group_id,header_component_group_id', 'integer', 'exists:App\Models\LinkedComponents\LinkedComponentGroup,id']
        ];
    }
}
