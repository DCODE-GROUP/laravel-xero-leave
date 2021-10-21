<?php

namespace Dcodegroup\LaravelXeroLeave\Jobs;

use Dcodegroup\LaravelXeroLeave\BaseXeroLeaveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncLeavetoXero implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Model $leave;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $leave)
    {
        $this->queue = config('laravel-xero-leave.queue');
        $this->leave = $leave;
    }

    public function handle()
    {
        $service = resolve(BaseXeroLeaveService::class);
        $service->sendLeaveToXero($this->leave);
    }
}