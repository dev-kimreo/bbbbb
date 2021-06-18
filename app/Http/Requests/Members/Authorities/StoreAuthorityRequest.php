<?php

namespace App\Http\Requests\Members\Authorities;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreAuthorityRequest extends FormRequest
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
            'code' => 'required|unique:authorities,code',
            'title' => 'required',
            'memo' => 'sometimes',
            'display_name' => 'required'
        ];
    }
}
