<?php

namespace App\Http\Requests\Inquiries;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|between:1,50',
            'id' => 'nullable|integer',
            'status' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'title' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:App\Models\Users\User,id',
            'user_email' => 'nullable|string',
            'user_name' => 'nullable|string',
            'assignee_id' => 'nullable|integer|exists:App\Models\Users\User,id|exists:App\Models\Manager,user_id',
            'assignee_name' => 'nullable|string',
            'answer_id' => 'nullable|integer|exists:App\Models\Users\User,id|exists:App\Models\Manager,user_id',
//            'multi_search' => 'nullable'
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
