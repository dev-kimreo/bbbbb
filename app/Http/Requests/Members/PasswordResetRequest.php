<?php

namespace App\Http\Requests\Members;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PasswordResetRequest extends FormRequest
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
            'email' => 'required|email|exists:App\Models\User,email',
            'token' => 'required',
            'password' => 'required|string|min:8',
            'passwordConfirmation' => 'required|string|same:password'
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'email.required' => getErrorCode(100001, 'email'),
            'email.email' => getErrorCode(100101, 'email'),
            'email.exists' => getErrorCode(100021, 'email'),
            'token.required' => getErrorCode(100001, 'token'),

            'password.required' => getErrorCode(100001, 'password'),
            'password.min' => getErrorCode(100063, 'password'),

            'passwordConfirmation.required' => getErrorCode(100001, 'passwordConfirmation'),
            'passwordConfirmation.same' => getErrorCode(100011, 'passwordConfirmation', 'password'),
        ];
    }
//
    protected function failedValidation(Validator $validator) {
        $resErr = getValidationErrToArr($validator->errors());
        throw new HttpResponseException(response()->json($resErr, 422));
    }


}
