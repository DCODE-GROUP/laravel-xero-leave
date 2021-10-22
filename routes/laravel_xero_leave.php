<?php

use Dcodegroup\LaravelXeroLeave\Http\Controllers\RetrySyncController;
use Dcodegroup\LaravelXeroLeave\Http\Controllers\UpdateStatusController;
use Illuminate\Support\Facades\Route;

Route::patch('update-status/{leave}', UpdateStatusController::class)->name('update-status');
Route::get('retry-sync/{leave}', RetrySyncController::class)->name('retry-sync');