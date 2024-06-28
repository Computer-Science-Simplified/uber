<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveRequest;
use App\Http\Requests\CreateRideRequest;
use App\Http\Requests\DropOffRequest;
use App\Http\Requests\PickUpRequest;
use App\Http\Resources\RideResource;
use App\Models\Driver;
use App\Models\Ride;
use App\Notifications\RideRequestedNotification;
use App\Services\DriverService;
use App\ValueObjects\Location;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class RideController extends Controller
{
    public function store(CreateRideRequest $request, DriverService $driverService)
    {
        $ride = Ride::createWaiting(
            $request->user(),
            $request->getLocation(),
        );

        try {
            $closestDriver = $driverService->getClosestDriver($ride);

            $closestDriver->notify(new RideRequestedNotification($ride));
        } catch (ModelNotFoundException) {}

        return response(
            [
                'data' => RideResource::make($ride)
            ],
            Response::HTTP_CREATED
        );
    }

    public function approve(Ride $ride, ApproveRequest $request)
    {
        $ride->approved($request->getDriver(), $request->getCar());

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function pickUp(Ride $ride, PickUpRequest $request)
    {
        $ride->inProgress();

        Redis::srem('drivers:available', $request->driver_id);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function dropOff(Ride $ride, DropOffRequest $request)
    {
        $ride->finished($request->getLocation());

        Redis::sadd('drivers:available', $ride->driver->id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
