<?php

namespace App\Http\Requests\Exhibitions\Popups;

use App\Models\Exhibitions\Exhibition;
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
        $isRequiredTargetGrade = Rule::requiredIf($this->input('target_opt') == 'grade');
        $isRequiredTargetUser = Rule::requiredIf($this->input('target_opt') == 'designate');

        return [
            'user_id' => ['prohibited'],
            'exhibition_category_id' => ['required', 'exists:App\Models\Exhibitions\ExhibitionCategory,id'],
            'title' => ['required', 'string'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date', 'after_or_equal:started_at'],
            'target_opt' => ['required', 'string', Rule::in(Exhibition::$targetOpt)],
            'target_grade' => [$isRequiredTargetGrade, 'array', Rule::in(Exhibition::$targetGrade)],
            'target_users' => [$isRequiredTargetUser, 'array'],
            'target_users.*' => ['integer', 'exists:App\Models\Users\User,id'],
            'sort' => ['nullable', 'integer', 'between:0,999'],
            'visible' => ['nullable', 'boolean'],
            'contents' => ['required', 'array'],
        ];
    }
}
