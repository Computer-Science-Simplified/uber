<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\UpdateCurrentLocationController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return 'ok';
});

Route::patch('/drivers/{driver}/current-location', UpdateCurrentLocationController::class);
Route::patch('/drivers/{driver}/check-in', CheckInController::class);
Route::get('/foo/{ride}', [\App\Http\Controllers\RideController::class, 'foo']);
