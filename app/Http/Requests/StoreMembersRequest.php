<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'name.required' => getResponseError(10000),
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
        $erros = $validator->errors()->toArray();

        $resErr = array();

        foreach ($erros as $key => $err) {

            $msg = array_shift($err);

            $errArrs = json_decode($msg, true);

            if (is_array($errArrs)) {
                $resErr['statusCode'][$errArrs['code']] = array(
                    'key' => $key,
                    'message' => $errArrs['message']
                );
            } else {
                $resErr[$key] = $msg;
            }

        }

        throw new HttpResponseException(response()->json(['errors' => $resErr], 422));
    }


}
