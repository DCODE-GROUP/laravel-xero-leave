<?php

namespace Dcodegroup\LaravelXeroLeave\Listeners;

use Dcodegroup\LaravelXeroLeave\Events\SendLeaveToXero;
use Dcodegroup\LaravelXeroLeave\Jobs\SyncLeavetoXero;

class SendToXeroListener
{
    public function handle(SendLeaveToXero $event)
    {
        SyncLeavetoXero::dispatch($event->leave);
    }
}
