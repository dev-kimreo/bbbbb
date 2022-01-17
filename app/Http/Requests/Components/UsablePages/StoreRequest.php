<?php

namespace App\Http\Requests\Components\UsablePages;

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
            'component_id' => ['required', 'integer', 'exists:App\Models\Components\Component,id'],
            'supported_editable_page_id' => ['required', 'integer', 'exists:App\Models\SupportedEditablePage,id'],
        ];
    }
}
