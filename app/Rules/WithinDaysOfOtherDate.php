<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class WithinDaysOfOtherDate implements Rule
{
    public $other;
    public $diff;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($other, $diff = 30)
    {
        $this->other = Carbon::parse($other);
        $this->diff = $diff;
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
        $date = Carbon::parse($value);

        // passed
        return $this->other->diffInDays($date) <= abs($this->diff);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('exception.common.within_days_of_other_date', ['day' => $this->diff]);
    }
}
