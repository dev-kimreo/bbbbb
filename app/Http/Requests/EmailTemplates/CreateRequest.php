<?php

namespace App\Http\Requests\EmailTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'code' => ['required', Rule::unique('App\Models\EmailTemplate', 'code')->where(function ($q){
                return $q->where('deleted_at', NULL);
            })],
            'name' => ['required'],
            'enable' => ['nullable', 'boolean'],
            'title' => ['required'],
            'content' => ['required'],
        ];
    }
}
