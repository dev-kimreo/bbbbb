<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
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
        return Auth::check() && !Auth::user()->isLoginToManagerService();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required_without_all:question,assigneeId,referrerId|string|between:6,100',
            'question' => 'required_without_all:title,assigneeId,referrerId|string|min:10',
            'assigneeId' => 'required_without_all:title,question,referrerId|integer|exists:App\Models\Manager,user_id',
            'referrerId' => 'required_without_all:title,question,assigneeId|integer|exists:App\Models\Manager,user_id'
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
