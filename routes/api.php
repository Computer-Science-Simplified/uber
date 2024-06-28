<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\UpdateCurrentLocationController;
use Illuminate\Support\Facades\Route;

Route::post('/rides', [RideController::class, 'store'])
    ->name('rides.store')
    ->middleware('auth:sanctum');

Route::patch('/drivers/{driver}/current-location', UpdateCurrentLocationController::class)->name('drivers.update-current-location');
Route::patch('/drivers/{driver}/check-in', CheckInController::class)->name('drivers.check-in');
Route::get('/drivers/{driver}/status', [DriverController::class, 'status']);
Route::patch('rides/{ride}/accept', [RideController::class, 'accept'])->name('rides.accept');
Route::patch('rides/{ride}/pick-up', [RideController::class, 'pickUp'])->name('rides.pick-up');
Route::patch('rides/{ride}/drop-off', [RideController::class, 'dropOff'])->name('rides.drop-off');
