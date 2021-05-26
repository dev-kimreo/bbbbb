<?php

namespace App\Http\Requests\Boards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetPostsCountRequest extends FormRequest
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
            'sortBy' => 'nullable|string',
            'email' => 'nullable|string',
            'name' => 'nullable|string',
            'postId' => 'nullable|integer',
            'title' => 'nullable|string',
            'multiSearch' => 'nullable'
        ];
    }

}
