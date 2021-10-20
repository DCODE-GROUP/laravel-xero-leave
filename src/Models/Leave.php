<?php

namespace Dcodegroup\LaravelXeroLeave\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var bool|string[]
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'xero_synced_at' => 'date',
        'xero_periods' => 'json',
        'is_approved' => 'boolean',
    ];

    public function leavable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeFailedXeroSync(Builder $query): Builder
    {
        return $query->whereNotNull('xero_exception_message');
    }

    //public function hasSuccessfullySynced(): bool
    //{
    //
    //}
}
