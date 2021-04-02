<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetListPostsRequest extends FormRequest
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
            'boardInfo' => 'sometimes|in:0,1',
            'page' => 'sometimes|integer|min:1',
            'view' => 'sometimes|integer', #|in:12,15
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'boardNo.exists' => getErrorCode(10001),
        ];
    }
//
    protected function failedValidation(Validator $validator) {
        $resErr = getValidationErrToArr($validator->errors());
        throw new HttpResponseException(response()->json($resErr, 422));
    }


}
