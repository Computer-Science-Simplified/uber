<?php

namespace App\Services;

use App\Enums\DriverStatus;
use App\Enums\RedisKey;
use App\Models\Driver;
use App\ValueObjects\Eta;
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
    public function getOnHoldDriverIds(?Eta $etaMin = null, ?Eta $etaMax = null): Collection
    {
        if (!$etaMin) {
            $etaMin = Eta::oneMinute();
        }

        if (!$etaMax) {
            $etaMax = Eta::fifteenMinutes();
        }

        return collect(
            Redis::zrevrangebyscore(
                RedisKey::DriverPoolOnHold->value,
                $etaMax->timestamp,
                $etaMin->timestamp,
            ),
        );
    }

    public function markAsAvailable(Driver $driver): void
    {
        Redis::sadd(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolUnavailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolOnHold->value, $driver->id);
    }

    public function markAsUnavailable(Driver $driver, ?Eta $eta = null): void
    {
        if (!$eta) {
            $eta = Eta::fifteenMinutes();
        }

        Redis::zadd(RedisKey::DriverPoolUnavailable->value, $eta->timestamp, $driver->id);

        Redis::srem(RedisKey::DriverPoolAvailable->value, $driver->id);

        Redis::zrem(RedisKey::DriverPoolOnHold->value, $driver->id);
    }

    public function markAsOnHold(Driver $driver, ?Eta $eta = null): void
    {
        if (!$eta) {
            $eta = Eta::fiveMinutes();
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
