<?php

return [
    /*
    * --------------------------------------------------------------------------
    * Laravel Xero Leave Job Queue
    * --------------------------------------------------------------------------
    *
    * This will allow you to configure queue to use for background jobs
    *
    */

    'queue_name' => env('LARAVEL_XERO_LEAVE_QUEUE_NAME', 'default'),

    /*
     * The assumption is this will be the model used for timesheets.
     * You should update this to match your timesheet model. Should be this
     */
    'leave_model' => Dcodegroup\LaravelXeroLeave\Models\Leave::class,

    /*
     * The name of the base layout to wrap the pages in.
     * The exposed routes will have to know the layout of the app in order to
     * Appear to look like the rest of the site.
     */

    'admin_app_layout' => env('LARAVEL_XERO_LEAVE_APP_LAYOUT', 'layouts.admin'),

    /*
     * Some apps require leave to be approved others do not. We provide the functionality to select if a leave request
     * should automatically be sent to Xero or if it should only be sent via another method.
     *
     * false = The event  <INSERT EVENT HERE> will send the request to xero when its created.
     * true = the event  <INSERT EVENT HERE> will not be automatically fired so the job will not be dispatched
     *
     */

    'applications_require_approval' => env('LARAVEL_XERO_LEAVE_APPLICATIONS_REQUIRE_APPROVAL', true),

    /*
     *  --------------------------------------------------------------------------
     * Laravel Xero Leave default hours
     * --------------------------------------------------------------------------
     *  The default number of hours one work day involves
     */

    'default_work_hours' => env('LARAVEL_XERO_LEAVE_DEFAULT_WORK_HOURS', 7.6),

    /*
     * ROUTES
     *
     * Below is the configuration for routes
     */

    /*
   * --------------------------------------------------------------------------
   * Laravel Xero Leave Path
   * --------------------------------------------------------------------------
   *
   * This is the URI path where Laravel Xero Timesheet Sync will be accessible from.
   * Feel free to change this path to anything you like.
   *
   */

    'path' => env('LARAVEL_XERO_LEAVE_PATH', 'xero-leave'),

    /*
    * --------------------------------------------------------------------------
    * Laravel Xero Leave AS
    * --------------------------------------------------------------------------
    *
    * This is the URI name prefix Laravel Xero Timesheet Sync will use in routes.
    * Feel free to change this path to anything you like.
    *
    */

    'as' => env('LARAVEL_XERO_LEAVE_AS', 'xero_leave'),

    /*
    * --------------------------------------------------------------------------
    * Laravel Xero Leave Route Middleware
    * --------------------------------------------------------------------------
    *
    * These middleware will get attached onto each Laravel Xero Timesheet Sync route, giving you
    * the chance to add your own middleware to this list or change any of
    * the existing middleware. Or, you can simply stick with this list.
    *
    */

    'middleware' => [
        'web',
        'auth',
    ],
];
