<?php

namespace App\Http\Requests\Inquiries;

use Illuminate\Foundation\Http\FormRequest;

class AssignRequest extends FormRequest
{
    public function all($keys = null)
    {
        $request = parent::all($keys);
        $request['assignee_id'] = $this->route('assignee_id');
        return $request;
    }

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
            'assignee_id' => 'required|integer|exists:App\Models\Manager,user_id'
        ];
    }

    public function messages()
    {
        // TODO - 다국어 적용
        // __('exceptions.validation.inquiry.assignee_id.exists')
        return [
            'assignee_id.exists' => '해당 사용자가 존재하지 않거나 또는 관리자가 아닙니다.'
        ];
    }
}
