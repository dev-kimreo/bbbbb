<?php

namespace App\Http\Requests\EditablePages;

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
            'supported_editable_page_id' => ['required', 'integer', 'exists:App\Models\SupportedEditablePage,id'],
            'name' => ['nullable', 'string'],
        ];
    }
}
