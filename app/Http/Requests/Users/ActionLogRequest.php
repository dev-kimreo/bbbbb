<?php

namespace App\Http\Requests\Users;

use App\Rules\WithinDaysOfOtherDate;
use Illuminate\Foundation\Http\FormRequest;

class ActionLogRequest extends FormRequest
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:started_at', new WithinDaysOfOtherDate($this->input('start_date'), 90)]
        ];
    }
}
