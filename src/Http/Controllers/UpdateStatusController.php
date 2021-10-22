<?php

namespace Dcodegroup\LaravelXeroLeave\Http\Controllers;

use App\Http\Controllers\Controller;
use Dcodegroup\LaravelXeroLeave\Http\Requests\UpdateStatus;
use Dcodegroup\LaravelXeroLeave\Models\Leave;
use Illuminate\Http\JsonResponse;

class UpdateStatusController extends Controller
{
    public function __invoke(UpdateStatus $request, Leave $leave): JsonResponse
    {
        $leave->{$request->input('action')}();

        return response()->json(['status' => $leave->fresh()->status]);
    }
}
