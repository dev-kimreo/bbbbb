<?php

namespace App\Http\Requests\Members\Managers;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreManagerRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id|unique:managers,user_id',
            'authority_id' => 'required|integer|exists:authorities,id,deleted_at,NULL'
        ];
    }
}
