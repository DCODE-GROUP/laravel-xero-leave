<?php

use Dcodegroup\LaravelXeroLeave\Http\Controllers\UpdateStatusController;
use Illuminate\Support\Facades\Route;

Route::patch('update-status/{leave}', UpdateStatusController::class)->name('update-status');