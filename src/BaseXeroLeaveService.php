<?php

namespace Dcodegroup\LaravelXeroLeave;

use App\Models\User;
use Dcodegroup\LaravelXeroLeave\Exceptions\XeroMissingemployeeIdException;
use Dcodegroup\LaravelXeroLeave\Models\Leave;
use Dcodegroup\LaravelXeroOauth\BaseXeroService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use XeroPHP\Models\PayrollAU\LeaveApplication;
use XeroPHP\Models\PayrollAU\PayItem;

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

    public function save(Request $request, Model $leave = null): Model|Leave|null
    {
        $leave = $leave ?: new Leave();
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

        return $leave->fresh();
    }

    /**
     * This is called from src/Jobs/SyncLeavetoXero.php and error handling and result of this action is handled in the
     * job. This allows us to not retry if it failed due to some exception.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $leave
     *
     * @return mixed|\XeroPHP\Remote\Model|null
     * @throws \Dcodegroup\LaravelXeroLeave\Exceptions\XeroMissingemployeeIdException
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
            ], $leaveParameters, );
        }

        return $this->saveModel(LeaveApplication::class, $leaveParameters, $objects);
    }

    private function createPeriod(Model $leave)
    {


    }
}
