<?php

namespace App\Http\Requests\Components;

use App\Models\Components\Component;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'solution_id' => ['sometimes', 'integer', 'exists:App\Models\Solution,id'],
            'first_category' => ['nullable', 'string', Rule::in(array_keys(Component::$firstCategory))],
            'second_category' => ['nullable', 'string', Rule::in(array_keys(Component::$secondCategory))],
            'sort_by' => ['nullable', 'string'],
        ];
    }
}
