<?php

namespace App\Http\Requests\Tooltips;

use App\Models\Tooltip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
            'title' => ['required', 'string', 'between:6,100'],
            'type' => ['required', Rule::in(Tooltip::$prefixes)],
            'visible' => ['nullable', 'boolean'],
            'content' => ['nullable', 'array']
        ];
    }
}
