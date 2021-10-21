<?php

namespace Dcodegroup\LaravelXeroLeave\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class LeaveDeclined
{
    use SerializesModels;

    public Model $leave;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Model $leave)
    {
        $this->leave = $leave;
    }
}
