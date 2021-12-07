<?php

namespace App\Http\Requests\Components\Options;

use App\Models\Components\ComponentType;
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
            'component_type_id' => ['required', Rule::exists(ComponentType::class, 'id')],
            'name' => ['required', 'string'],
            'key' => ['required', 'string'],
            'display_on_pc' => ['boolean'],
            'display_on_mobile' => ['boolean'],
            'hideable' => ['required', 'boolean'],
            'help' => ['string'],
            'attributes' => ['string']
        ];
    }
}
