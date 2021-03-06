<?php

namespace App\Http\Requests\BackofficeMenus\Permissions;

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
            'authority_id' => 'required|integer|exists:App\Models\Authority,id',
            'backoffice_menu_id' => 'required|integer|exists:App\Models\BackofficeMenu,id'
        ];
    }
}
