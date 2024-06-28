<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Services\DriverPoolService;

class DriverController extends Controller
{
    public function status(Driver $driver, DriverPoolService $driverPool)
    {
        return [
            'data' => [
                'status' => $driverPool->getStatus($driver),
            ],
        ];
    }
}
