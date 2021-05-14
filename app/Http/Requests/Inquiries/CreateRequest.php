<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            'title' => 'required|string|between:6,100',
            'question' => 'required|string|min:10',
//            'attachFiles.id' => [
//                'sometimes',
//                'array',
//                Rule::exists('App\Models\AttachFile', 'id')->where('attachable_type', 'temp')
//            ],
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'title.required' => getErrorCode(100001, 'title'),
            'title.between' => getErrorCode(100053, 'title'),

            'question.required' => getErrorCode(100001, 'question'),
            'question.min' => getErrorCode(100063, 'question'),

//            'thumbnail.id.integer' => getErrorCode(100041, 'thumbnail.id'),
//            'thumbnail.id.exists' => getErrorCode(100021, 'thumbnail.id'),
        ];
    }
}
