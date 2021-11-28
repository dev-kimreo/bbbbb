<?php

namespace App\Http\Requests\Components\Versions;

use App\Models\Components\Component;
use App\Models\Components\ComponentVersion;
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
            'usable' => ['prohibited'],
            'template' => ['sometimes', 'string'],
            'style' => ['sometimes', 'string'],
            'script' => ['sometimes' => 'string'],
        ];
    }
}
