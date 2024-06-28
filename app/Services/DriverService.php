<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Ride;
use App\ValueObjects\Location;
use Illuminate\Support\Facades\Redis;

class DriverService
{
    public function getNearbyDriver(Ride $ride): Driver
    {
        $availableDriverIds = collect(Redis::smembers('drivers:available'));

        /** @var Location $location */
        $location = $ride->pick_up_location;

        $nearbyDriverIds = collect(Redis::georadius('drivers:current-locations', $location->longitude, $location->latitude, 5, 'km'));

        $results = $availableDriverIds->intersect($nearbyDriverIds);

        return Driver::findOrFail($results->first());
    }
}
