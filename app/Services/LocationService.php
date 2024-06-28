<?php

namespace App\Services;

use App\Enums\RedisKey;
use App\Models\Driver;
use App\Models\Ride;
use App\ValueObjects\Location;
use Illuminate\Support\Facades\Redis;

class LocationService
{
    public function __construct(private readonly DriverPoolService $driverPool)
    {
    }

    public function getClosestDriver(Ride $ride): Driver
    {
        $availableDriverIds = $this->driverPool->getAvailableDriverIds();

        /** @var Location $location */
        $location = $ride->pick_up_location;

        $nearbyDriverIds = collect(
            Redis::georadius(RedisKey::DriverCurrentLocations->value, $location->longitude, $location->latitude, 5, 'km')
        );

        $results = $availableDriverIds->intersect($nearbyDriverIds);

        return Driver::findOrFail($results->first());
    }

    public function updateCurrentLocation(Driver $driver, Location $location): void
    {
        Redis::geoadd(RedisKey::DriverCurrentLocations->value, $location->longitude, $location->latitude, $driver->id);
    }
}
