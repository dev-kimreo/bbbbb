<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GetListRequest extends FormRequest
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
            'board_id' => 'nullable|integer',
            'email' => 'nullable|string',
            'name' => 'nullable|string',
            'post_id' => 'nullable|integer',
            'title' => 'nullable|string',
            'sort_by' => 'nullable|string',
            'multi_search' => 'nullable',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|between:1,100',
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
