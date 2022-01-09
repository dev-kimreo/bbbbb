<?php

namespace App\Http\Requests\Words;

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
            'code' => ['sometimes', Rule::unique('App\Models\Word', 'code')->where(function ($q){
                return $q->where('deleted_at', NULL);
            })],
            'title' => ['sometimes', 'string']
        ];
    }
}
