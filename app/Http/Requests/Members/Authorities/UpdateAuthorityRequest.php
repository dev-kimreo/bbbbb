<?php

namespace App\Http\Requests\Members\Authorities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAuthorityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check() && Auth::user()->checkAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required_without_all:title,display_name',
            'title' => 'required_without_all:code,display_name',
            'display_name' => 'required_without_all:code,title'
        ];
    }
}
