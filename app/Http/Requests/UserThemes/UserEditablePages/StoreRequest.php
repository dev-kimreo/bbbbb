<?php

namespace App\Http\Requests\UserThemes\UserEditablePages;

use App\Models\SupportedEditablePage;
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
            'supported_editable_page_id' => ['required', Rule::exists(SupportedEditablePage::class, 'id')],
            'name' => ['required', 'string']
        ];
    }
}
