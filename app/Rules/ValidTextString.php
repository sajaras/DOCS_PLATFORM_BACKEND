<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidTextString implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * The rule fails if the input string contains any HTML tags.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // `preg_match` returns 1 if the pattern is found, 0 if not, and false on error.
        // We want the validation to pass if no HTML tags are found (returns 0).
        return preg_match('/<[^>]*>/', $value) === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field cannot contain HTML tags.';
    }
}