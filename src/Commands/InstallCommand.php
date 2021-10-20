<?php

namespace Dcodegroup\LaravelXeroLeave\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-xero-leave:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Laravel Xero Leave requirements';

    public function handle()
    {
        $this->comment('Publishing Laravel Xero Leave Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'laravel-xero-leave-config']);

        $this->comment('Publishing Laravel Xero Leave Migrations');
        $this->callSilent('vendor:publish', ['--tag' => 'laravel-xero-leave-table-migrations']);

        $this->info('Laravel Xero Leave scaffolding installed successfully.');
    }
}
