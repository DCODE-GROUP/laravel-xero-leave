<?php

namespace Dcodegroup\LaravelXeroLeave;

use Dcodegroup\LaravelXeroLeave\Commands\AutoUpdateXeroConfigurationData;
use Dcodegroup\LaravelXeroLeave\Commands\InstallCommand;
use Dcodegroup\LaravelXeroLeave\Observers\LaravelXeroLeaveObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use XeroPHP\Application;

class LaravelXeroLeaveServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->offerPublishing();
        $this->registerCommands();

        $leaveClass = config('laravel-xero-leave.leave_model');
        $leaveClass::observe(new LaravelXeroLeaveObserver());

        //$this->registerResources();
        //$this->registerRoutes();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-xero-leave.php', 'laravel-xero-leave');

        $this->app->bind(BaseXeroLeaveService::class, function () {
            return new BaseXeroLeaveService(resolve(Application::class));
        });
    }

    /**
     * Setup the resource publishing groups for Dcodegroup Xero Timesheets.
     */
    protected function offerPublishing()
    {
        $this->publishes([__DIR__.'/../config/laravel-xero-leave.php' => config_path('laravel-xero-leave.php')], 'laravel-xero-leave-config');

        if (!Schema::hasTable('leaves')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_leaves_table.stub.php' => database_path('migrations/'.$timestamp.'_create_leaves_table.php'),
            ], 'laravel-xero-leave-table-migrations');
        }
    }

    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                AutoUpdateXeroConfigurationData::class,
            ]);
        }
    }
}
