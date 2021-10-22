<?php

namespace App\Http\Requests\Posts;

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
            'board_id' => 'required_without_all:title,content,sort,hidden|integer|exists:App\Models\Boards\Board,id',
            'title' => 'required_without_all:board_id,content,sort,hidden|string|between:6,128',
            'content' => 'required_without_all:board_id,title,sort,hidden|string|min:10',
            'sort' => 'required_without_all:board_id,title,content,hidden|integer|between:1,999',
            'hidden' => 'required_without_all:board_id,title,content,sort|boolean',
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
