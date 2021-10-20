<?php

namespace Dcodegroup\LaravelXeroLeave;

use Dcodegroup\LaravelXeroOauth\BaseXeroService;
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
}
