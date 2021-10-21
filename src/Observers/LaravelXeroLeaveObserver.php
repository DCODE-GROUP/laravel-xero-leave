<?php

namespace Dcodegroup\LaravelXeroLeave\Observers;

use Dcodegroup\LaravelXeroLeave\Events\SendLeaveToXero;
use Illuminate\Database\Eloquent\Model;

class LaravelXeroLeaveObserver
{
    public function created(Model $leave)
    {
        if ($leave->is_approve) {
            event(new SendLeaveToXero($leave));
        }
    }
}
