<?php

namespace Dcodegroup\LaravelXeroLeave\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasLeave
{
    public function leave(): MorphMany
    {
        return $this->morphMany(config('laravel-xero-leave.leave_model'),'leavable' );
    }
}