<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRideRequest;
use App\Http\Requests\DropOffRequest;
use App\Models\Driver;
use App\Models\Ride;
use App\ValueObjects\Location;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class RideController extends Controller
{
    public function store(CreateRideRequest $request)
    {
        $ride = Ride::createWaiting(
            $request->user(),
            $request->getLocation(),
        );

        return response($ride, Response::HTTP_CREATED);
    }

    public function foo(Ride $ride)
    {
        $availableDriverIds = collect(Redis::smembers('drivers:available'));

        /** @var Location $location */
        $location = $ride->pick_up_location;

        $nearbyDriverIds = collect(Redis::georadius('drivers:current-locations', $location->longitude, $location->latitude, 5, 'km'));

        $results = $availableDriverIds->intersect($nearbyDriverIds);

        return Driver::find($results->first());
    }

    public function pickUp(Ride $ride)
    {
        $ride->inProgress();

        Redis::sdel('drivers:available', $ride->driver->id);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function dropOff(Ride $ride, DropOffRequest $request)
    {
        $ride->finished($request->getLocation());

        Redis::sadd('drivers:available', $ride->driver->id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
