<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
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
            'perPage' => 'nullable|integer|between:1,50',
            'id' => 'nullable|integer',
            'status' => 'nullable|string',
            'startDate' => 'nullable|date_format:Y-m-d',
            'endDate' => 'nullable|date_format:Y-m-d',
            'title' => 'nullable|string',
            'user_id' => 'nullable|integer|exists:App\Models\Manager,user_id',
            'user_email' => 'nullable|string',
            'user_name' => 'nullable|string',
            'assignee_id' => 'nullable|integer|exists:App\Models\Manager,user_id',
            'assignee_name' => 'nullable|string',
            'multiSearch' => 'nullable'
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
