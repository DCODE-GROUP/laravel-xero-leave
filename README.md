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
is_approved tinyint(1) DEFAULT=0
xero_periods json NULL
xero_exception_message text NULL
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

## Jobs



## Commands

`laravel-xero-leave:install` publishes the configuration file and the migrations

`laravel-xero-leave:update-xero-configuration-data` will fetch and store the leave types in the database. You can use `--force
 to ensure it runs now.

## Resources

There is no ability to delete a leave application through the Xero API. More information here [https://developer.xero.com/documentation/api/payrollau/leaveapplications/#currently-unsupported-features](https://developer.xero.com/documentation/api/payrollau/leaveapplications/#currently-unsupported-features).