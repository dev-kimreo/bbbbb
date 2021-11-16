<?php

namespace App\Http\Requests\Components;

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
            'solution_id' => ['sometimes', 'integer', 'exists:App\Models\Solution,id'],
            'sort_by' => ['nullable', 'string'],
        ];
    }
}
