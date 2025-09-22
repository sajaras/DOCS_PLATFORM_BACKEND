<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // A robust regex for international phone numbers.
        return preg_match('/^(\+\d{1,3}[- ]?)?\d{10}$/', $value);
    }

    public function message()
    {
        return 'The :attribute must be a valid  phone number.';
    }
}