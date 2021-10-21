<?php

namespace Dcodegroup\LaravelXeroLeave\Observers;

use Dcodegroup\LaravelXeroLeave\Events\RequestLeaveApproval;
use Illuminate\Database\Eloquent\Model;

class LaravelXeroLeaveObserver
{
    public function created(Model $leave)
    {
        if (config('laravel-xero-leave.applications_require_approval')) {
            event(new RequestLeaveApproval($leave));
        } else {
            $leave->approve();
        }
    }

    public function updated(Model $leave)
    {
        logger($leave);
        logger('declined at changed: '.$leave->wasChanged('declined_at'));
        logger('approved at changed: '.$leave->wasChanged('approved_at'));
    }

    public function saved(Model $leave)
    {
        logger($leave);
        logger('declined at changed: '.$leave->wasChanged('declined_at'));
        logger('approved at changed: '.$leave->wasChanged('approved_at'));
    }
}
