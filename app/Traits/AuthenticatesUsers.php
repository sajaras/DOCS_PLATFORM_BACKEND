<?php

namespace App\Traits;

trait AuthenticatesUsers
{
    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'phone_number';
    }
}