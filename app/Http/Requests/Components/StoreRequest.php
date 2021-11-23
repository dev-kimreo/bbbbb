<?php

namespace App\Http\Requests\Components;

use App\Models\Components\Component;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\Components\ComponentUsagePageBySolution;

class StoreRequest extends FormRequest
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
            'solution_id' => ['required', 'integer', 'exists:App\Models\Solution,id'],
            'name' => ['required', 'string'],
            'use_other_than_maker' => ['prohibited'],
            'first_category' => ['required', 'string', Rule::in(array_keys(Component::$firstCategory))],
            'second_category' => ['exclude_if:first_category,theme_component', 'required', 'string', Rule::in(array_keys(Component::$secondCategory))],
            'use_blank' => ['required', 'boolean'],
            'use_all_page' => ['required', 'boolean', new ComponentUsagePageBySolution],
            'icon' => ['required', Rule::in(array_keys(Component::$icon))],
            'display' => ['required', 'boolean'],
            'status' => ['required', 'string', Rule::in(array_keys(Component::$status))],
            'manager_memo' => ['sometimes', 'string'],
        ];
    }
}
