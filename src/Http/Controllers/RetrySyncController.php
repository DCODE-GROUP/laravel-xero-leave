<?php

namespace Dcodegroup\LaravelXeroLeave\Http\Controllers;

use App\Http\Controllers\Controller;
use Dcodegroup\LaravelXeroLeave\Events\SendLeaveToXero;
use Dcodegroup\LaravelXeroLeave\Models\Leave;
use Illuminate\Http\JsonResponse;

class RetrySyncController extends Controller
{
    public function __invoke(Leave $leave): JsonResponse
    {
        event(new SendLeaveToXero($leave));

        return response()->json();
    }
}
