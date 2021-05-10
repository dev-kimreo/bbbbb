<?php

namespace App\Http\Requests\Admins\Boards;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateBoardsRequest extends FormRequest
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
            'name' => 'required|string|between:2,32',
            'type' => 'required|string|max:32|unique:App\Models\Board,type',
//            'options' => 'required|string|min:8',
        ];
    }

    /**
     * @return array
     * @description code {Number} - 20000 어쩌구저쩌구
     */
    public function messages()
    {
        return [
            'name.required' => getErrorCode(100001, 'name'),
            'name.between' => getErrorCode(100053, 'name'),

            'type.required' => getErrorCode(100001, 'type'),
            'type.max' => getErrorCode(100073, 'type'),
            'type.unique' => getErrorCode(100002, 'type'),
        ];
    }
}