<?php

namespace App\Http\Requests\Attaches;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App;

class CreateAttachRequest extends FormRequest
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
            "files.*" => 'required|mimes:jpg,jpeg,png,gif,webp',
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'files.*.required' => getErrorCode(100001, 'files'),
            'files.*.mimes' => getErrorCode(100155, 'files'),
        ];
    }
//
    protected function failedValidation(Validator $validator) {
        $resErr = getValidationErrToArr($validator->errors());

        throw new HttpResponseException(response()->json($resErr, 422));
    }


}