<?php

namespace App\Http\Requests\Exceptions;

use App\Rules\ArrayKeysInIso639_1;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RelationStoreRequest extends FormRequest
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
            'code' => ['required', Rule::unique('App\Models\Exception', 'code')->where(function ($q){
                return $q->where('deleted_at', NULL);
            })],
            'title' => ['required', 'string'],
            'value' => ['required', 'array', new ArrayKeysInIso639_1],
        ];
    }
}
