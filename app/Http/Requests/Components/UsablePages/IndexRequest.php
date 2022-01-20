<?php

namespace App\Http\Requests\Components\UsablePages;

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
            'component_id' => ['required_without_all:supported_editable_page_id', 'integer', 'exists:App\Models\Components\Component,id'],
            'supported_editable_page_id' => ['required_without_all:component_id', 'integer', 'exists:App\Models\SupportedEditablePage,id'],
        ];
    }
}
