<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\UpdateCurrentLocationController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return 'ok';
});

Route::patch('/drivers/{driver}/current-location', UpdateCurrentLocationController::class)->name('drivers.update-current-location');
Route::patch('/drivers/{driver}/check-in', CheckInController::class)->name('drivers.check-in');
Route::get('/drivers/{driver}/status', [\App\Http\Controllers\DriverController::class, 'status']);
Route::post('/ride', [\App\Http\Controllers\RideController::class, 'store'])->name('rides.store');
Route::patch('rides/{ride}/approve', [\App\Http\Controllers\RideController::class, 'approve'])->name('rides.approve');
Route::patch('rides/{ride}/pick-up', [\App\Http\Controllers\RideController::class, 'pickUp'])->name('rides.pick-up');
