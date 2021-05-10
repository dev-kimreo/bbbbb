<?php

namespace App\Http\Requests\Members;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ModifyMemberPwdRequest extends FormRequest
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
            'password' => 'required',
            'changePassword' => 'required|string|min:8',
            'passwordConfirmation' => 'required|string|same:changePassword'
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'password.required' => getErrorCode(100001, 'password'),
            'changePassword.required' => getErrorCode(100001, 'changePassword'),
            'changePassword.min' => getErrorCode(100063, 'changePassword'),
            'passwordConfirmation.required' => getErrorCode(100001, 'passwordConfirmation'),
            'passwordConfirmation.same' => getErrorCode(100011, 'passwordConfirmation', 'changePassword'),
        ];
    }
}