<?php

namespace Dcodegroup\LaravelXeroLeave;

use App\Models\User;
use Carbon\Carbon;
use Dcodegroup\LaravelConfiguration\Models\Configuration;
use Dcodegroup\LaravelXeroLeave\Exceptions\XeroMissingDefaultCalendarIDException;
use Dcodegroup\LaravelXeroLeave\Exceptions\XeroMissingEmployeeIdException;
use Dcodegroup\LaravelXeroOauth\BaseXeroService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use XeroPHP\Models\PayrollAU\LeaveApplication;
use XeroPHP\Models\PayrollAU\PayItem;
use XeroPHP\Models\PayrollAU\PayrollCalendar;

class BaseXeroLeaveService extends BaseXeroService
{
    /**
     * Actually have this same method in BaseXeroPayrollAuService However I am not requiring that in this package.
     *
     * @return null|\Illuminate\Support\Collection|\XeroPHP\Remote\Collection|\XeroPHP\Remote\Model|\XeroPHP\Remote\Query
     */
    public function getLeaveTypes()
    {
        return $this->getModel(PayItem::class, null, 'LeaveTypes');
    }

    public function save(Request $request, Model $leave = null): Model
    {
        $leaveClass = config('laravel-xero-leave.leave_model');
        $leave = $leave ?: new $leaveClass();
        $user = User::findOrFail($request->input('leaveable_id'));

        $leave->fill($request->only([
            'title',
            'description',
            'start_date',
            'end_date',
            'xero_leave_type_id',
            'units',
        ]));

        // Validate the units
        if ($request->input('start_date') != $request->input('end_date')) {
            // if the dates are not the same day then clear any value
            $leave->units = null;
        }

        $leave->xero_employee_id = $user->xero_employee_id;

        $user->leave()->save($leave);

        return $leave;
    }

    /**
     * This is called from src/Jobs/SyncLeavetoXero.php and error handling and result of this action is handled in the
     * job. This allows us to not retry if it failed due to some exception.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $leave
     *
     * @return mixed|\XeroPHP\Remote\Model|null
     * @throws \Dcodegroup\LaravelXeroLeave\Exceptions\XeroMissingEmployeeIdException
     */
    public function sendLeaveToXero(Model $leave)
    {
        if (empty($leave->leaveable->xero_employee_id)) {
            throw new XeroMissingEmployeeIdException($leave->leaveble);
        }

        $objects = [];

        if ($leave->start_date->eq($leave->end_date) && ! empty($leave->units)) {
            /*
             * is the same day so we need to work out the period.
             * units are not empty so its less than a day
             */
            $objects = $this->createPeriod($leave);
            logger($objects);
        }

        $leaveParameters = [
            'EmployeeID' => $leave->xero_employee_id,
            'LeaveTypeID' => $leave->xero_leave_type_id,
            'StartDate' => $leave->start_date,
            'EndDate' => $leave->end_date,
            'Title' => $leave->title,
            'Description' => $leave->description ?? '',
        ];

        if ($leave->hasXeroLeaveApplicationId()) {
            return $this->updateModel(LeaveApplication::class, (object) [
                'identifier' => 'LeaveApplicationID',
                'guid' => $leave->xero_leave_application_id,
            ], $leaveParameters, $objects);
        }

        return $this->saveModel(LeaveApplication::class, $leaveParameters, $objects);
    }

    private function createPeriod(Model $leave)
    {
        // Employee calendar or default

        $calendarId = $leave->leaveable->xero_default_payroll_calendar_id;

        if (empty($calendarId)) {
            throw new XeroMissingDefaultCalendarIDException($leave->leaveable);
        }

        $calendar = $this->getCalendar($calendarId);

        $calendarPeriodStarts = $this->buildCalendarPeriodStartDates($calendar);

        $periodDates = collect($calendarPeriodStarts)->map(function ($periodStart) use ($calendar, $leave) {
            $periodEnd = $periodStart->copy()->{$this->getMethodForCalendarType($calendar)}()->subDay();

            return [
                'periodStart' => $periodStart,
                'periodEnd' => $periodEnd,
            ];
        })->first(function ($item, $key) use ($leave) {
            return $leave->start_date->between($item['periodStart'], $item['periodEnd']);
        });

        $period = new LeaveApplication\LeavePeriod();
        $period->setNumberOfUnit($leave->units);
        $period->setPayPeriodStartDate($periodDates['periodStart']);
        $period->setPayPeriodEndDate($periodDates['periodEnd']);

        return ['LeavePeriod' => $period];
    }

    private function getCalendar(string $payrollCalendarId)
    {
        return collect(Configuration::byKey('xero_payroll_calendars')
                                    ->get()
                                    ->pluck('value')
                                    ->first())->first(function ($value, $key) use ($payrollCalendarId) {
                                        return data_get($value, 'PayrollCalendarID') == $payrollCalendarId;
                                    });
    }

    /**
     * This is taken from dcodegroup/laravel-xero-timesheet-sync PayrollCalendarService.
     */
    private function generateCalendarPeriods(string $payrollCalendarId = null): array
    {
        if (is_null($payrollCalendarId)) {
            return [];
        }

        $calendar = $this->getCalendar($payrollCalendarId);

        $calendarPeriodStarts = $this->buildCalendarPeriodStartDates($calendar);

        return collect($calendarPeriodStarts)->map(function ($periodStart) use ($calendar) {
            $periodEnd = $periodStart->copy()->{$this->getMethodForCalendarType($calendar)}()->subDay();

            return [
                'value' => $periodStart->toDateString().'||'.$periodEnd->toDateString(),
                'label' => $periodStart->format('j M Y').' '.$periodEnd->format('j M Y'),
            ];
        })->toArray();
    }

    /**
     * This is taken from dcodegroup/laravel-xero-timesheet-sync PayrollCalendarService.
     */
    private function getReferenceDate(array $calendar): Carbon
    {
        return Carbon::parse(data_get($calendar, 'ReferenceDate'));
    }

    /**
     * This is taken from dcodegroup/laravel-xero-timesheet-sync PayrollCalendarService.
     */
    private function getCalendarType(array $calendar): string
    {
        return data_get($calendar, 'CalendarType');
    }

    private function generatePaymentDatesUntil(array $calendar): string
    {
        return Carbon::parse(data_get($calendar, 'PaymentDate'))->addYear()->format('Y-m-d');
    }

    /**
     * This is taken from dcodegroup/laravel-xero-timesheet-sync PayrollCalendarService.
     * generatePaymentDatesUntil method if modified though
     */
    private function buildCalendarPeriodStartDates($calendar)
    {
        $date = $this->getReferenceDate($calendar);

        switch ($this->getCalendarType($calendar)) {
            case PayrollCalendar::CALENDARTYPE_WEEKLY:
                return call_user_func_array([
                                                $date,
                                                'weeksUntil',
                                            ], [$this->generatePaymentDatesUntil($calendar)]);

            case PayrollCalendar::CALENDARTYPE_FORTNIGHTLY:
            case PayrollCalendar::CALENDARTYPE_TWICEMONTHLY:
                return call_user_func_array([
                                                $date,
                                                'fortnightUntil',
                                            ], [
                                                $this->generatePaymentDatesUntil($calendar),
                                            ]);

            case PayrollCalendar::CALENDARTYPE_MONTHLY:
            case PayrollCalendar::CALENDARTYPE_FOURWEEKLY:
                return call_user_func_array([
                                                $date,
                                                'monthsUntil',
                                            ], [$this->generatePaymentDatesUntil($calendar)]);

            case PayrollCalendar::CALENDARTYPE_QUARTERLY:
                return call_user_func_array([
                                                $date,
                                                'quartersUntil',
                                            ], [$this->generatePaymentDatesUntil($calendar)]);
        }
    }

    /**
     * This is taken from dcodegroup/laravel-xero-timesheet-sync PayrollCalendarService.
     */
    private function getMethodForCalendarType(array $calendar): string
    {
        switch ($this->getCalendarType($calendar)) {
            case PayrollCalendar::CALENDARTYPE_WEEKLY:
                return 'addWeek';

            case PayrollCalendar::CALENDARTYPE_FORTNIGHTLY:
            case PayrollCalendar::CALENDARTYPE_TWICEMONTHLY:
                return 'addFortnight';

            case PayrollCalendar::CALENDARTYPE_MONTHLY:
            case PayrollCalendar::CALENDARTYPE_FOURWEEKLY:
                return 'addMonth';

            case PayrollCalendar::CALENDARTYPE_QUARTERLY:
                return 'addQuarter';

            default:
                return 'no-type';
        }
    }
}
