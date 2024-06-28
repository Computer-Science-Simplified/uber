<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Facades\Redis;

class DriverPoolService
{
    public function markAsAvailable(Driver $driver): void
    {
        Redis::sadd('drivers:available', $driver->id);

        Redis::zrem('drivers:unavailable', $driver->id);
    }

    public function markAsUnavailable(Driver $driver, int $eta = 10 * 60): void
    {
        Redis::srem('drivers:available', $driver->id);

        Redis::zadd('drivers:unavailable', $eta, $driver->id);
    }

    public function isAvailable(Driver $driver): bool
    {
        return Redis::sismember('drivers:available', $driver->id)
            && !(!!Redis::zscore('drivers:unavailable', $driver->id));
    }
}
