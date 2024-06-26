<?php

use App\Http\Controllers\UpdateCurrentLocationController;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return 'ok';
});

Route::patch('/drivers/{driver}/current-location', UpdateCurrentLocationController::class);
