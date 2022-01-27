<?php

namespace App\Http\Requests\Users\UserSites;

use App\Exceptions\QpickHttpException;
use App\Models\Users\UserSolution;
use App\Rules\Matched;
use Auth;
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
        return Auth::isLoggedForBackoffice() or Auth::id() == $this->route('user_id');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws QpickHttpException
     */
    public function rules()
    {
        return [
            'user_solution_id' => [
                'nullable',
                Rule::exists(UserSolution::class, 'id'),
                new Matched(UserSolution::class, 'user_id', Auth::id(), '사용자 아이디')
            ],
            'name' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'biz_type' => ['nullable', 'string']
        ];
    }
}
