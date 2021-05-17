<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

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
            'title' => 'required_without_all:question,attachFiles,delFiles|string|between:6,100',
            'question' => 'required_without_all:title,attachFiles,delFiles|string|min:10',
            'attachFiles.id.*' => [
                'sometimes',
                'integer',
                Rule::exists('App\Models\AttachFile', 'id')->where('attachable_type', 'temp')
            ],
            'delFiles.id.*' => 'sometimes|integer|exists:App\Models\AttachFile,id',
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
        ];
    }
}
