<?php

namespace App\Http\Requests\Users;

use App\Models\Solution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SiteRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'user_id' => ['prohibited'],
            'solution_id' => [Rule::exists(Solution::class, 'id')],
            'type' => ['string', 'max:16'],
            'name' => ['string', 'max:32'],
            'url' => ['url', 'max:256'],
            'solution' => ['string', 'max:16'],
            'apikey' => ['string', 'max:512'],
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
