# Laravel Xero Leave

This package provides the standard xero functionality for sending leave applications to Xero.

This package provides
* Tables for leave
* depends on dcodegroup/laravel-xero-employee
* events for approval
* config for approval or not
* Configuration for the types of leave
* commands to keep settings updated.
* button to re-sync


## How it works

Follow the installation instructions. 
Select set if you want to approval requests
The events that are fired

## Events

* Leave application created
* Leave Approval Required
* Leave Approved - has a listener fire send to xero
* 

## Installation

You can install the package via composer:

```bash
composer require dcodegroup/laravel-xero-leave
```

Then run the install command.

```bash
php artsian laravel-xero-leave:install
```

This will publish the configuration file and the migrations.

Then run the migrations

```bash
php artsian migrate
```

The following table will be added to your project


```yaml
leaves
---
id bigint(20) PK IDENTITY
leavable_type varchar(255)
leaveable_id unsignedbigint
xero_leave_application_id varchar(50) NULL # The identifier returned from xero
xero_employee_id varchar(50) NULL # may be redundant becuase its on the user that should be the polymporphic field. But saves a lookup
xero_leave_type_id varchar(50) # The identifier returned from xero stored in the configuration
start_date date
end_date date
units double(8,2) # in case the duration is less than a day
title varchar(50)
description varchar(200) NULL
xero_periods json NULL
xero_exception_message text NULL
approved_at timestamp NULL
declined_at timestamp NULL
xero_synced_at timestamp NULL
deleted_at timestamp NULL
created_at timestamp NULL
updated_at timestamp NULL
```

## Configuration

Most of configuration has been set the fair defaults. However you can review the configuration file at `config/laravel-xero-leave.php` and adjust as needed

## Usage

You should implement your own frontend to this package. However the model and the events etc are already fired for you. Below will guide you through what you need to do.

Create your own `Leave::class` model class that extends the packages model.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Dcodegroup\LaravelXeroLeave\Models\Leave as BaseLeave;

class Leave extends BaseLeave
{
    use HasFactory;
    ...
```

Ensure to add the following trait to the model you will use leave with. eg `App\Models\User::class`

```php

class User extends Authenticatable 
{
    use HasLeave;
    ...
```

Example Vue component to update the status of leave that require approval

```vue
<template>
  <td>
    <menu>
      <ul>
        <li>
          <button class="button primary active">
            {{ currentStatus }}
          </button>
          <ul class="right">
            <li>
              <a @click="submit('approve')">
                Approve
              </a>
            </li>
            <li>
              <a @click="submit('decline')">
                Decline
              </a>
            </li>
            <li>
              <a @click="submit('pending')">
                Pending
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </menu>
  </td>
</template>

<script>
export default {
    name: "UpdateLeaveStatus",

    props: {
        rowData: {
            type: Object,
            required: true
        },
        rowIndex: {
            type: Number
        },
        rowField: {
            type: [String, Object]
        },
    },

    data() {
        return {
            currentStatus: this.rowData.status,
        }
    },

    methods: {
        submit(value) {
            axios.patch(this.rowData.update_status_url, {
                action: value,
            }).then(({data}) => {
                this.currentStatus = data.status;
            }).catch((errors) => {
                console.error(errors);
            });
        }
    }
}
</script>

<style scoped>

</style>
```

## Routes

Currently there is one route that accompanies the above vue component

```bash
+--------+--------+----------------------------------+--------------------------+---------+----------------------------------+
| Domain | Method | URI                              | Name                     | Action  | Middleware                       |
+--------+--------+----------------------------------+--------------------------+---------+----------------------------------+
|        | PATCH  | xero-leave/update-status/{leave} | xero_leave.update-status | Closure | web                              |
|        |        |                                  |                          |         | App\Http\Middleware\Authenticate |
+--------+--------+----------------------------------+--------------------------+---------+----------------------------------+
```

## Events 

Communicating with Xero works by firing events. These events have listeners which will fire the listeners and dispatch jobs.

All events accept the `Leave::class` as the only parameter.

`SendLeaveToXero::class` send the leave record to Xero. This has a listener blah then then dispatches the job SendToXero
`LeaveApproved::class` When the leave is changed to approved it will fire this event. This will trigger the sending leave to xero
`LeaveDeclined::class` When the leave is changed to un-approved it will fire this event. This will trigger the sending leave to xero (normally an update of the current application)
`RequestLeaveApproval::class` If the configuration parameter `laravel-xero-leave.applications_require_approval` is true and a new leave request is created then this event will be fired. You can listen to it in your own application fire a notification or email informing who action needs taking.

A request class is already present in this package. You can update and use the code below for your store and update classes in your own controllers.

```php

use App\Http\Controllers\Controller;
use Dcodegroup\LaravelXeroLeave\BaseXeroLeaveService;
use Dcodegroup\LaravelXeroLeave\Http\Requests\StoreLeave;

class LeaveController extends Controller
{
    protected BaseXeroLeaveService $service;

    public function __construct()
    {
        $this->service = resolve(BaseXeroLeaveService::class);
    }

    public function store(StoreLeave $request)
    {
        $this->authorize('create', Leave::class);

        $this->service->save($request);
        
        ...        
    }

    public function update(StoreLeave $request, Model $leave)
    {
        $this->authorize('update', $leave);

        $this->service->save($request, $leave);
        
        ....
    }

    ...
```


## Jobs



## Commands

`laravel-xero-leave:install` publishes the configuration file and the migrations

`laravel-xero-leave:update-xero-configuration-data` will fetch and store the leave types in the database. You can use `--force
 to ensure it runs now.


You should add it to your `app/Console/Kernel.php` file to run it once a day. You could run it more often if wanted with the --force flag

```php
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('laravel-xero-leave:update-xero-configuration-data')->daily();    
        ...
    }

```

## Resources

There is no ability to delete a leave application through the Xero API. More information here [https://developer.xero.com/documentation/api/payrollau/leaveapplications/#currently-unsupported-features](https://developer.xero.com/documentation/api/payrollau/leaveapplications/#currently-unsupported-features).