<?php

namespace App\Http\Controllers;

use App\Enums\RedisKey;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class DriverController extends Controller
{
    public function status(Driver $driver)
    {
        $isAvailable = Redis::sismember(RedisKey::DriverPoolAvailable->value, $driver->id);

        return [
            'available' => $isAvailable,
        ];
    }
}
