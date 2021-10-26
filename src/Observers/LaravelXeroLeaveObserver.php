<?php

namespace Dcodegroup\LaravelXeroLeave\Observers;

use Dcodegroup\LaravelXeroLeave\Events\LeaveRequested;
use Dcodegroup\LaravelXeroLeave\Events\SendLeaveToXero;
use Illuminate\Database\Eloquent\Model;

class LaravelXeroLeaveObserver
{
    public function created(Model $leave)
    {
        if (config('laravel-xero-leave.applications_require_approval')) {
            event(new LeaveRequested($leave));
        } else {
            $leave->approve();
        }
    }

    public function updated(Model $leave)
    {
        if ($leave->shouldUpdateXero()) {
            event(new SendLeaveToXero($leave));
        }
    }
}
