<?php

namespace App\Services;

use App\Enums\DriverStatus;
use App\Enums\RedisKey;
use App\Models\Car;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

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
    public function getUnavailableDriverIds(?Carbon $etaMin = null, ?Carbon $etaMax = null): Collection
    {
        if (!$etaMin) {
            $etaMin = now();
        }

        if (!$etaMax) {
            $etaMax = now()->addMinutes(15);
        }

        return collect(
            Redis::zrangebyscore(
                RedisKey::DriverPoolUnavailable->value,
                $etaMax->timestamp,
                $etaMin->timestamp,
            ),
        );
    }

    public function moveToAvailable(Driver $driver): void
    {
        Redis::sadd(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolUnavailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolOnHold->value, $driver->id);
    }

    public function moveToUnavailable(Driver $driver, ?Car $eta = null): void
    {
        if (!$eta) {
            $eta = now()->addMinutes(15);
        }

        Redis::zadd(RedisKey::DriverPoolUnavailable->value, $eta->timestamp, $driver->id);

        Redis::srem(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolOnHold->value, $driver->id);
    }

    public function moveToOnHold(Driver $driver, ?Carbon $eta = null): void
    {
        if (!$eta) {
            $eta = now()->addMinutes(15);
        }

        Redis::zadd(RedisKey::DriverPoolOnHold->value, $eta->timestamp, $driver->id);

        Redis::srem(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolUnavailable->value, $driver->id);
    }

    public function remove(Driver $driver): void
    {
        Redis::zrem(RedisKey::DriverPoolOnHold->value, $driver->id);

        Redis::srem(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolUnavailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverCurrentLocations->value, $driver->id);
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

        return DriverStatus::NotInPool;
    }
}
