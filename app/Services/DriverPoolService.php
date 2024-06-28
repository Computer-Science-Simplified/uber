<?php

namespace App\Services;

use App\Enums\DriverStatus;
use App\Enums\RedisKey;
use App\Models\Driver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use UnexpectedValueException;

class DriverPoolService
{
    /**
     * @return Collection<Driver>
     */
    public function getAvailableDriverIds(): Collection
    {
        return collect(Redis::smembers(RedisKey::DriverPoolAvailable->value));
    }

    /**
     * @return Collection<Driver>
     */
    public function getOnHoldDriverIds(int $etaMin = 1 * 60, $etaMax = 15 * 60): Collection
    {
        return collect(Redis::zrevrangebyscore(RedisKey::DriverPoolOnHold, $etaMin, $etaMax));
    }

    public function markAsAvailable(Driver $driver): void
    {
        Redis::sadd(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolUnavailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolOnHold->value, $driver->id);
    }

    public function markAsUnavailable(Driver $driver, int $eta = 15 * 60): void
    {
        Redis::zadd(RedisKey::DriverPoolUnavailable->value, $eta, $driver->id);

        Redis::srem(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolOnHold->value, $driver->id);
    }

    public function markAsOnHold(Driver $driver, int $eta = 5 * 60): void
    {
        Redis::zadd(RedisKey::DriverPoolOnHold->value, $eta, $driver->id);

        Redis::srem(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolUnavailable->value, $driver->id);
    }

    public function getStatus(Driver $driver): DriverStatus
    {
        if (Redis::sismember(RedisKey::DriverPoolAvailable->value, $driver->id)) {
            return DriverStatus::Available;
        }

        if (!!Redis::zscore(RedisKey::DriverPoolOnHold->value, $driver->id)) {
            return DriverStatus::OnHold;
        }

        if (!!Redis::zscore(RedisKey::DriverPoolUnavailable->value, $driver->id)) {
            return DriverStatus::Unavailable;
        }

        throw new UnexpectedValueException("Invalid status for driver [$driver->id]");
    }
}
