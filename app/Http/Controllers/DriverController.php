<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DriverController extends Controller
{
    public function status(Driver $driver)
    {
        $isAvailable = Redis::sismember('drivers:available', $driver->id);

        return [
            'available' => $isAvailable,
        ];
    }
}
