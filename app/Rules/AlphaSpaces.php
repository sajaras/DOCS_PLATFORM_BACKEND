<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AlphaSpaces implements Rule
{
    public function passes($attribute, $value)
    {
        return preg_match('/^[\pL\s]+$/u', $value);
    }

    public function message()
    {
        return 'The :attribute must only contain letters and spaces.';
    }
}