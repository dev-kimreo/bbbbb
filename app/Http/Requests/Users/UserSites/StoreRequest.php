<?php

namespace App\Http\Requests\Users\UserSites;

use App\Models\Users\UserSolution;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::isLoggedForBackoffice() or Auth::id() == $this->route('user_id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_solution_id' => ['nullable', Rule::exists(UserSolution::class, 'id')],
            'name' => ['required', 'string'],
            'url' => ['required', 'url'],
            'biz_type' => ['required', 'string']
        ];
    }
}
