<?php

namespace App\Http\Requests\Exhibitions\Categories;

use App\Models\Exhibitions\ExhibitionCategory;
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
            'name' => ['required', 'string'],
            'url' => ['required', 'url'],
            'division' => ['required', 'string', Rule::in(ExhibitionCategory::$divisions)],
            'site' => ['required', 'string', Rule::in(ExhibitionCategory::$sites)],
            'max' => ['nullable', 'integer', 'between:0,999'],
            'enable' => ['nullable', 'boolean'],
        ];
    }
}
