<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ModifyPostsRequest extends FormRequest
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
            'title' => 'required_without_all:content,thumbnail,delFiles|string|between:6,128',
            'content' => 'required_without_all:title,thumbnail,delFiles|string|min:10',
            'thumbnail.id' => [
                'sometimes',
                'integer',
                Rule::exists('App\Models\AttachFile', 'id')->where('attachable_type', 'temp')
            ],
            'delFiles.*' => 'sometimes|integer|exists:App\Models\AttachFile,id',
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
