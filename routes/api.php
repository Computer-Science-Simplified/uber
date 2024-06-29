<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\RideController;
use Illuminate\Support\Facades\Route;

Route::post('/rides', [RideController::class, 'store'])
    ->name('rides.store')
    ->middleware('auth:sanctum');

Route::patch('/drivers/{driver}/current-location', [DriverController::class, 'updateCurrentLocation'])->name('drivers.update-current-location');
Route::patch('/drivers/{driver}/check-in', [DriverController::class, 'checkIn'])->name('drivers.check-in');
Route::patch('/drivers/{driver}/check-out', [DriverController::class, 'checkOut'])->name('drivers.check-out');
Route::patch('rides/{ride}/accept', [RideController::class, 'accept'])->name('rides.accept');
Route::patch('rides/{ride}/pick-up', [RideController::class, 'pickUp'])->name('rides.pick-up');
Route::patch('rides/{ride}/drop-off', [RideController::class, 'dropOff'])->name('rides.drop-off');
