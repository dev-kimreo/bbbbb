<?php

namespace App\Http\Requests\Attaches;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App;

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
        return [
            "type" => "required",
            "typeId" => "required",
            "thumbnail" => "sometimes|in:1"
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'type.required' => getErrorCode(100001, 'type'),
            'typeId.required' => getErrorCode(100001, 'typeId'),
            'thumbnail.in' => getErrorCode(100081, 'thumbnail'),
        ];
    }
}
