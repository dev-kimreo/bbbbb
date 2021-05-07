<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreatePostsRequest extends FormRequest
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
            'title' => 'required|string|between:6,128',
            'content' => 'required|string|min:10',
            'thumbnail' => [
                'sometimes',
                'integer',
                Rule::exists('App\Models\AttachFile', 'id')->where('type', 'temp')
            ],
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

            'content.required' => getErrorCode(100001, 'content'),
            'content.min' => getErrorCode(100063, 'content'),

            'thumbnail.integer' => getErrorCode(100041, 'thumbnail'),
            'thumbnail.exists' => getErrorCode(100021, 'thumbnail'),
        ];
    }
}