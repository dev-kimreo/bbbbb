<?php

namespace App\Rules\Components;

use Illuminate\Contracts\Validation\Rule;

class ComponentUsagePageBySolution implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // 공통 솔루션 선택 시 사용페이지 선택 사용 불가
        if (request()->solution_id == 1) {
            return $value;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('component.disable.selected_pages_for_common_solution');
    }
}
