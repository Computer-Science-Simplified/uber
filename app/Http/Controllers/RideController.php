<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveRequest;
use App\Http\Requests\CreateRideRequest;
use App\Http\Requests\DropOffRequest;
use App\Http\Requests\PickUpRequest;
use App\Http\Resources\RideResource;
use App\Models\Ride;
use App\Notifications\RideRequestedNotification;
use App\Services\DriverPoolService;
use App\Services\LocationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\Response;

class RideController extends Controller
{
    public function __construct(private DriverPoolService $driverPool)
    {
    }

    public function store(CreateRideRequest $request, LocationService $driverService)
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
        $driver = $request->getDriver();

        $ride->approved($driver, $request->getCar());

        $this->driverPool->markAsOnHold($driver);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function pickUp(Ride $ride, PickUpRequest $request)
    {
        $ride->inProgress();

        $this->driverPool->markAsUnavailable($request->getDriver());

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function dropOff(Ride $ride, DropOffRequest $request)
    {
        $ride->finished($request->getLocation());

        $this->driverPool->markAsAvailable($ride->driver);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
