<?php

namespace App\Http\Requests\Themes;

use App\Models\Themes\Theme;
use Illuminate\Foundation\Http\FormRequest;
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
            'theme_product_id' => ['prohibited'],
            'solution_id' => ['prohibited'],
            'status' => ['sometimes', 'string', Rule::in(Theme::$status)],
            'display' => ['sometimes', 'boolean']
        ];
    }
}
