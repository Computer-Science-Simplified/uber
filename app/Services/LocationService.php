<?php

namespace App\Services;

use App\Enums\DriverStatus;
use App\Enums\RedisKey;
use App\Models\Driver;
use App\Models\Ride;
use App\ValueObjects\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class LocationService
{
    public function __construct(private readonly DriverPoolService $driverPool)
    {
    }

    /**
     * @return Collection<Driver>
     */
    public function getClosestDrivers(Location $location, DriverStatus $status, int $radius = 5): Collection
    {
        $possibleDriverIds = match ($status) {
            DriverStatus::Available => $this->driverPool->getAvailableDriverIds(),
            DriverStatus::OnHold => $this->driverPool->getOnHoldDriverIds(),
        };

        $nearbyDriverIds = collect(
            Redis::georadius(
                RedisKey::DriverCurrentLocations->value,
                $location->longitude,
                $location->latitude,
                $radius,
                'km',
            ),
        );

        return Driver::find($possibleDriverIds->intersect($nearbyDriverIds));
    }

    public function updateCurrentLocation(Driver $driver, Location $location): void
    {
        Redis::geoadd(RedisKey::DriverCurrentLocations->value, $location->longitude, $location->latitude, $driver->id);
    }
}
