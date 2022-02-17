<?php

namespace App\Http\Requests\UserThemes\UserEditablePageLayouts;

use App\Models\UserThemes\UserComponentGroup;
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
            'header_component_group_id' => ['required', Rule::exists(UserComponentGroup::class, 'id')],
            'content_component_group_id' => ['required', Rule::exists(UserComponentGroup::class, 'id')],
            'footer_component_group_id' => ['required', Rule::exists(UserComponentGroup::class, 'id')],
        ];
    }
}
