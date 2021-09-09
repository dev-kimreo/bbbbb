<?php

namespace App\Http\Requests\Widgets;

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
            'user_id' => ['prohibited'],
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'enable' => ['nullable', 'boolean'],
            'only_for_manager' => ['nullable', 'boolean'],
        ];
    }
}
