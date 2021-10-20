# Laravel Xero Leave

This package provides the standard xero functionality for sending leave applications to Xero.

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

** Update
This will add the following fields to timesheets table and create two new tables.

```yaml
xero_leave
---



```

## Configuration

Most of configuration has been set the fair defaults. However you can review the configuration file at `config/laravel-xero-leave.php` and adjust as needed

