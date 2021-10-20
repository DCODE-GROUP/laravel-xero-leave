<?php

namespace Dcodegroup\LaravelXeroLeave\Jobs;

use Dcodegroup\LaravelConfiguration\Models\Configuration;
use Dcodegroup\LaravelXeroLeave\BaseXeroLeaveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncLeaveTypesConfigurationOptions implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = config('laravel-xero-leave.queue');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = resolve(BaseXeroLeaveService::class);

        $leaveTypes = $service->getLeaveTypes();
        Configuration::byKey('xero_leave_types')->update(['value' => $leaveTypes->toArray()]);
    }
}
