<?php

namespace App\Services;

use App\Enums\DriverStatus;
use App\Models\Driver;
use Illuminate\Support\Facades\Redis;
use UnexpectedValueException;

class DriverPoolService
{
    public function markAsAvailable(Driver $driver): void
    {
        Redis::sadd('drivers:available', $driver->id);

        Redis::zrem('drivers:unavailable', $driver->id);

        Redis::zrem('drivers:on-hold', $driver->id);
    }

    public function markAsUnavailable(Driver $driver, int $eta = 15 * 60): void
    {
        Redis::zadd('drivers:unavailable', $eta, $driver->id);

        Redis::srem('drivers:available', $driver->id);

        Redis::zrem('drivers:on-hold', $driver->id);
    }

    public function markAsOnHold(Driver $driver, int $eta = 5 * 60): void
    {
        Redis::zadd('drivers:on-hold', $eta, $driver->id);

        Redis::srem('drivers:available', $driver->id);

        Redis::zrem('drivers:unavailable', $driver->id);
    }

    public function getStatus(Driver $driver): DriverStatus
    {
        if (Redis::sismember('drivers:available', $driver->id)) {
            return DriverStatus::Available;
        }

        if (!!Redis::zscore('drivers:on-hold', $driver->id)) {
            return DriverStatus::OnHold;
        }

        if (!!Redis::zscore('drivers:unavailable', $driver->id)) {
            return DriverStatus::Unavailable;
        }

        throw new UnexpectedValueException("Invalid status for driver [$driver->id]");
    }
}
