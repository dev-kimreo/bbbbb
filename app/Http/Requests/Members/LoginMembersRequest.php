<?php

namespace App\Http\Requests\Members;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginMembersRequest extends FormRequest
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
            'email' => 'required|string|email|max:100|exists:App\Models\User,email',
            'password' => 'required|string|min:8'
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'email.exists' => getErrorCode(10301),
//            'name.between' => json_encode([
//                'code' => 20001,
//                'message' => __('validation.required')
//            ]),
//            'password.required' => json_encode([
//                'code' => 20003,
//                'message' => __('validation.required')
//            ]),
//            'email' => __('validation.unique')
        ];
    }
//
    protected function failedValidation(Validator $validator) {
        $resErr = getValidationErrToArr($validator->errors());
        throw new HttpResponseException(response()->json($resErr, 422));
    }


}