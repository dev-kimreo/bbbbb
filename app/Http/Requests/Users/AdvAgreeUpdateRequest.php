<?php

namespace App\Http\Requests\Users;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class AdvAgreeUpdateRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check() && (Auth::user()->isLoginToManagerService() || Auth::id() == $this->route('user_id'));
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'agree' => ['required', 'integer', 'between:0,1']
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
        ];
    }
}
