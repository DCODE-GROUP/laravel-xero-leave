<?php

namespace Dcodegroup\LaravelXeroLeave\Jobs;

use Dcodegroup\LaravelXeroLeave\BaseXeroLeaveService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use XeroAPI\XeroPHP\ApiException;
use XeroPHP\Models\PayrollAU\LeaveApplication;

class SyncLeavetoXero implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Model $leave;

    /**
     * Create a new job instance.
     */
    public function __construct(Model $leave)
    {
        $this->queue = config('laravel-xero-leave.queue');
        $this->leave = $leave;
    }

    public function handle()
    {
        $service = resolve(BaseXeroLeaveService::class);
        $response = $service->sendLeaveToXero($this->leave);

        logger('response: '.json_encode($response));

        if ($response instanceof ApiException && $response->getCode() == 429) {
            report($response);
            $secondsRemaining = $response->getResponseObject()->header('Retry-After');

            Cache::put('xero-api-limit', now()->addSeconds($secondsRemaining)->timestamp, $secondsRemaining);

            $this->release($secondsRemaining);
        }

        if ($response instanceof Exception) {
            report($response);
            $this->leave->update([
                'xero_exception_message' => $response->getMessage(),
                'xero_exception' => json_encode($response),
            ]);

            $this->fail();
        }

        if ($response instanceof LeaveApplication) {
            if ($response->hasGUID()) {
                $this->leave->update([
                    'xero_synced_at' => now(),
                    'xero_leave_application_id' => $response->getLeaveApplicationID(),
                    'xero_exception_message' => null,
                    'xero_exception' => null,
                    'xero_periods' => $response->getLeavePeriods(),
                ]);
            } else {
                $this->leave->update([
                    'xero_exception_message' => 'The LeaveApplication was returned but did not have the LeaveApplicationID',
                    'xero_exception' => json_encode($response),
                ]);
            }
        }
    }
}
