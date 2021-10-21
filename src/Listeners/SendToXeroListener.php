<?php

namespace Dcodegroup\LaravelXeroLeave\Listeners;

use Dcodegroup\LaravelXeroLeave\BaseXeroLeaveService;
use Dcodegroup\LaravelXeroLeave\Events\SendLeaveToXero;

class SendToXeroListener
{
    public function handle(SendLeaveToXero $event)
    {
        $service = resolve(BaseXeroLeaveService::class);
    }
}
