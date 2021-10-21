<?php

namespace Dcodegroup\LaravelXeroLeave;

use App\Models\User;
use Dcodegroup\LaravelXeroLeave\Exceptions\XeroMissingemployeeIdException;
use Dcodegroup\LaravelXeroLeave\Models\Leave;
use Dcodegroup\LaravelXeroOauth\BaseXeroService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use XeroAPI\XeroPHP\Models\PayrollAu\LeaveApplication;
use XeroPHP\Models\PayrollAU\PayItem;

class BaseXeroLeaveService extends BaseXeroService
{
    /**
     * Actually have this same method in BaseXeroPayrollAuService However I am not requiring that in this package
     *
     * @return \Illuminate\Support\Collection|\XeroPHP\Remote\Collection|\XeroPHP\Remote\Model|\XeroPHP\Remote\Query|null
     */
    public function getLeaveTypes()
    {
        return $this->getModel(PayItem::class, null, 'LeaveTypes');
    }

    public function save(Request $request, Model $leave = null)
    {
        $leave = $leave ?: new Leave();
        $user = User::findOrFail($request->input('user_id'));

        $leave->fill($request->only(['title' ,'description', 'start_date', 'end_date', 'xero_leave_type_id', 'units']));

        /*
         * Validate the units
         */
        if ($request->input('start_date') != $request->input('end_date')) {
            // if the dates are not the same day then clear any value
            $leave->units = null;
        }

        $leave->xero_employee_id = $user->xero_employee_id;

        if (! config('laravel-xero-leave.applications_require_approval')) {
            $leave->is_approve = true;
        }

        $leave->save();
    }

    public function sendLeaveToXero(Model $leave)
    {
        if (empty($leave->leaveable->xero_employee_id)) {
            throw new XeroMissingEmployeeIdException($leave->leaveble);
        }



        if ($leave->start_date->eq($leave->end_date) && ! empty($leave->units)) {
            /**
             * is the same day so we need to work out the period.
             * units are not empty so its less than a day
             */
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
            $response = $this->updateModel(
                LeaveApplication::class,
                (object) [
                    'identifier' => 'LeaveApplicationID',
                    'guid' => $leave->xero_leave_application_id,
                ],
                $leaveParameters,
            );
        } else {
            $response = $this->saveModel(
                LeaveApplication::class,
                $leaveParameters,
            );
        }


        //logger('response: '.json_encode($response));
    }

    private function createPeriod()
    {
    }
}
