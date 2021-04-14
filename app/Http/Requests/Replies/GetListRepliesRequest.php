<?php

namespace App\Http\Requests\Replies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetListRepliesRequest extends FormRequest
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
            'page' => 'sometimes|integer|min:1',
            'perPage' => 'sometimes|integer|between:1,50',
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'page.min' => getErrorCode(100063, 'page'),

            'perPage.between' => getErrorCode(100051, '{perPage}'),
        ];
    }
//
    protected function failedValidation(Validator $validator) {
        $resErr = getValidationErrToArr($validator->errors());
        throw new HttpResponseException(response()->json($resErr, 422));
    }


}
