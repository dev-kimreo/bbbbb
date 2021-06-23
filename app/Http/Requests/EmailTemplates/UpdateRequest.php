<?php

namespace App\Http\Requests\EmailTemplates;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'code' => ['prohibited'],
            'name' => ['required_without_all:enable,title,content', 'string'],
            'enable' => ['required_without_all:name,title,content', 'boolean'],
            'title' => ['required_without_all:name,enable,content', 'string'],
            'content' => ['required_without_all:name,enable,title', 'string'],
        ];
    }
}
