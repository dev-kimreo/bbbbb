<?php

namespace App\Http\Requests\BackofficeMenus;

use Illuminate\Foundation\Http\FormRequest;
use App;

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
            'name' => 'required|string|between:2,32',
            'parent' => 'sometimes|integer',
            'sort' => 'sometimes|integer'
        ];
    }
}
