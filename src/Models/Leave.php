<?php

namespace Dcodegroup\LaravelXeroLeave\Models;

use Dcodegroup\LaravelConfiguration\Models\Configuration;
use Dcodegroup\LaravelXeroLeave\Events\LeaveApproved;
use Dcodegroup\LaravelXeroLeave\Events\LeaveDeclined;
use Dcodegroup\LaravelXeroLeave\Events\SendLeaveToXero;
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
        'approved_at' => 'datetime',
        'declined_at' => 'datetime',
        'end_date' => 'date',
        'start_date' => 'date',
        'xero_periods' => 'json',
        'xero_synced_at' => 'datetime',
    ];

    public function leaveable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeFailedXeroSync(Builder $query): Builder
    {
        return $query->whereNotNull('xero_exception_message');
    }

    /**
     * @return mixed
     */
    public static function getLeaveTypeOptions()
    {
        return Configuration::byKey('xero_leave_types')->pluck('value')->flatten(1)->map(function ($item) {
            return [
                'label' => $item['Name'],
                'value' => $item['LeaveTypeID'],
            ];
        });
    }

    public static function getValidXeroLeaveTypes(): array
    {
        return Configuration::byKey('xero_leave_types')->pluck('value')->flatten(1)->pluck('LeaveTypeID')->toArray();
    }

    public function approve(): void
    {
        $this->update([
            'approved_at' => now(),
            'declined_at' => null,
        ]);

        if (config('laravel-xero-leave.applications_require_approval')) {
            event(new LeaveApproved($this));
        }

        event(new SendLeaveToXero($this));
    }

    public function decline(): void
    {
        $this->update([
            'approved_at' => null,
            'declined_at' => now(),
        ]);

        event(new LeaveDeclined($this));
    }

    public function pending(): void
    {
        $this->update([
            'approved_at' => null,
            'declined_at' => null,
        ]);
    }

    public function getStatusAttribute(): string
    {
        if (config('laravel-xero-leave.applications_require_approval') && empty($this->approved_at) && empty($this->declined_at)) {
            return __('laravel-xero-leave-translations::laravel-xero-leave.status.pending');
        }

        if (! empty($this->approved_at)) {
            return __('laravel-xero-leave-translations::laravel-xero-leave.status.approved');
        }

        if (! empty($this->declined_at)) {
            return __('laravel-xero-leave-translations::laravel-xero-leave.status.declined');
        }

        return __('laravel-xero-leave-translations::laravel-xero-leave.status.unknown');
    }

    public function hasXeroLeaveApplicationId(): bool
    {
        return ! empty($this->xero_leave_application_id);
    }

    public function hasSyncError(): bool
    {
        return ! empty($this->xero_exception_message);
    }

    public function shouldUpdateXero(): bool
    {

        if ($this->wasChanged(['start_date', 'end_date', 'units']) && $this->hasXeroLeaveApplicationId()) {
            if (config('laravel-xero-leave.applications_require_approval')) {
                return !empty($this->approved_at);
            }

            return true;
        }

        return false;
    }
}
