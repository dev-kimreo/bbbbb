<?php

namespace App\Http\Requests\Admins\Boards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ModifyBoardsRequest extends FormRequest
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
            'name' => 'required_without_all:hidden,options|string|between:2,32',
            'hidden' => 'required_without_all:name,options|in:0,1',
            'options' => 'required_without_all:name,hidden|array',
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'type.unique' => getErrorCode(20010),
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
