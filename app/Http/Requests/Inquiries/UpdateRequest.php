<?php

namespace App\Http\Requests\Inquiries;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

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
            'title' => 'required_without_all:question,assignee_id,referrer_id|string|between:6,100',
            'question' => 'required_without_all:title,assignee_id,referrer_id|string|min:10',
            'assignee_id' => 'required_without_all:title,question,referrer_id|integer|exists:App\Models\Manager,user_id',
            'referrer_id' => 'required_without_all:title,question,assignee_id|integer|exists:App\Models\Manager,user_id'
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
