<?php

namespace Dcodegroup\LaravelXeroLeave\Commands;

use Dcodegroup\LaravelConfiguration\Models\Configuration;
use Dcodegroup\LaravelXeroLeave\Jobs\SyncLeaveTypesConfigurationOptions;
use Illuminate\Console\Command;

class AutoUpdateXeroConfigurationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-xero-leave:update-xero-configuration-data
         {--force : force update now}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the configuration data for leave types from Xero';

    /**
     * @return void
     */
    public function handle()
    {
        $force = $this->option('force');
        $record = Configuration::query()->byKey('xero_leave_types')->first();

        if ($record->updated_at->gte(now()->subWeek()) || $force) {
            SyncLeaveTypesConfigurationOptions::dispatch();
            $this->info('Laravel Xero Leave Xero Configuration data updated.');
        } else {
            $this->info('Laravel Xero Leave Xero Configuration update not required.');
        }
    }
}
