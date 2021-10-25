<?php

namespace Dcodegroup\LaravelXeroLeave;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Dcodegroup\LaravelXeroLeave\Commands\AutoUpdateXeroConfigurationData;
use Dcodegroup\LaravelXeroLeave\Commands\InstallCommand;
use Dcodegroup\LaravelXeroLeave\Events\SendLeaveToXero;
use Dcodegroup\LaravelXeroLeave\Listeners\SendToXeroListener;
use Dcodegroup\LaravelXeroLeave\Observers\LaravelXeroLeaveObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
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

        Event::listen(SendLeaveToXero::class, SendToXeroListener::class);

        $this->registerResources();
        $this->registerRoutes();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-xero-leave.php', 'laravel-xero-leave');

        $this->app->bind(BaseXeroLeaveService::class, function () {
            return new BaseXeroLeaveService(resolve(Application::class));
        });

        $this->registerCarbonMacros();
    }

    /**
     * Setup the resource publishing groups for Dcodegroup Xero Timesheets.
     */
    protected function offerPublishing()
    {
        $this->publishes([__DIR__.'/../config/laravel-xero-leave.php' => config_path('laravel-xero-leave.php')], 'laravel-xero-leave-config');

        if (! Schema::hasTable('leaves')) {
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

    protected function registerResources()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-xero-leave-translations');
        //$this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-xero-leave-views');
    }

    protected function registerRoutes()
    {
        Route::group([
                         'prefix' => config('laravel-xero-leave.path'),
                         'as' => Str::slug(config('laravel-xero-leave.as'), '_').'.',
                         'middleware' => config('laravel-xero-leave.middleware', 'web'),
                     ], function () {
                         $this->loadRoutesFrom(__DIR__.'/../routes/laravel_xero_leave.php');
                     });
    }

    /**
     * This too has been copied from dcodegroup/laravel-xero-timesheet-sync and is another reason it should be
     * made into its own package
     */
    public function registerCarbonMacros()
    {
        Carbon::macro('addFortnight', function () {
            return $this->addWeeks(2);
        });

        Carbon::macro('fortnightUntil', function ($date) {
            return CarbonPeriod::create($this, '14 days', $date);
        });
    }
}
