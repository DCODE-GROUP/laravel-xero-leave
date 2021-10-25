<?php

namespace Dcodegroup\LaravelXeroLeave\Exceptions;

use App\Models\User;
use Exception;

class XeroMissingDefaultCalendarIDException extends Exception
{
    public function __construct(User $user)
    {
        parent::__construct("User {$user->xero_employee_name} must have a Xero CalendarID before you can continue.");
    }
}
