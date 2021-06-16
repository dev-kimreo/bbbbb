<?php

namespace App\Http\Requests\Members;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        if (Auth::hasAccessRightsToBackoffice()) {
            return [
                'name' => 'nullable|string|between:2,100',
                'password' => 'nullable',
                'memo_for_managers' => 'nullable|string'
            ];
        } else {
            return [
                'name' => 'required|string|between:2,100',
                'password' => 'required'
            ];
        }
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
        ];
    }
}
