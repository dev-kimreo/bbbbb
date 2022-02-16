<?php

namespace App\Http\Requests\TermsOfUse;

use App\Models\TermsOfUse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurrentRequest extends FormRequest
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
            'service' => ['required', Rule::in(array_keys(TermsOfUse::$services))],
            'type' => ['required', Rule::in(array_keys(TermsOfUse::$types))],
        ];
    }
}
