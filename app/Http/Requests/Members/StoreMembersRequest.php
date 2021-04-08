<?php

namespace App\Http\Requests\Members;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App;

class StoreMembersRequest extends FormRequest
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
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:App\Models\User,email',
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
            'name.required' => getErrorCode(100001, 'name'),
            'name.between' => getErrorCode(100053, 'name'),

            'email.required' => getErrorCode(100001, 'email'),
            'email.email' => getErrorCode(100101, 'email'),
            'email.max' => getErrorCode(100073, 'email'),
            'email.unique' => getErrorCode(100002, 'email'),

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
