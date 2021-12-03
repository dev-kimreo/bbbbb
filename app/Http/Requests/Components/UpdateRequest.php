<?php

namespace App\Http\Requests\Components;

use App\Models\Components\Component;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\Components\ComponentUsagePageBySolution;

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
            'solution_id' => ['sometimes', 'integer', 'exists:App\Models\Solution,id'],
            'name' => ['sometimes', 'string'],
            'use_other_than_maker' => ['prohibited'],
            'first_category' => ['sometimes', 'string', Rule::in(array_keys(Component::$firstCategory))],
            'second_category' => ['exclude_unless:first_category,design,product,solution,', 'required', 'string', Rule::in(array_keys(Component::$secondCategory))],
            'use_blank' => ['sometimes', 'boolean'],
            'use_all_page' => ['sometimes', 'boolean', new ComponentUsagePageBySolution],
            'icon' => ['required', Rule::in(array_keys(Component::$icon))],
            'display' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'string', Rule::in(array_keys(Component::$status))],
            'manager_memo' => ['sometimes', 'string'],
        ];
    }
}
