<?php

namespace App\Rules;

use App\Libraries\StringLibrary;
use Illuminate\Contracts\Validation\Rule;

class ArrayKeysInIso639_1 implements Rule
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
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // check array
        if (!is_array($value)) {
            return false;
        }

        // check keys
        foreach ($value as $k => $v) {
            if (!StringLibrary::chkIso639_1Code($k)) {
                return false;
            }
        }

        // passed
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('exception.common.wrong_language_code');
    }
}
