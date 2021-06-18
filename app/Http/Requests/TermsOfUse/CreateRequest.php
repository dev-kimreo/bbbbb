<?php

namespace App\Http\Requests\TermsOfUse;

use App\Models\TermsOfUse;
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
            'type' => ['required', Rule::in(TermsOfUse::$types)],
            'title' => ['required'],
            'content' => ['required', 'array'],
            'start_at' => ['required', 'date'],
            'history' => ['sometimes'],
        ];
    }
}