<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptRideRequest;
use App\Http\Requests\CreateRideRequest;
use App\Http\Requests\DropOffRequest;
use App\Http\Requests\PickUpRequest;
use App\Http\Resources\RideResource;
use App\Jobs\NotifyClosestAvailableDriversJob;
use App\Models\Ride;
use App\Services\DriverPoolService;
use Symfony\Component\HttpFoundation\Response;

class RideController extends Controller
{
    public function __construct(private DriverPoolService $driverPool)
    {
    }

    public function store(CreateRideRequest $request)
    {
        $ride = Ride::createWaiting(
            $request->user(),
            $request->getLocation(),
        );

        NotifyClosestAvailableDriversJob::dispatch($ride);

        return response(
            [
                'data' => RideResource::make($ride)
            ],
            Response::HTTP_CREATED
        );
    }

    public function accept(Ride $ride, AcceptRideRequest $request)
    {
        $driver = $request->getDriver();

        $ride->accepted($driver, $request->getCar());

        $this->driverPool->moveToOnHold($driver);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function pickUp(Ride $ride, PickUpRequest $request)
    {
        $ride->inProgress();

        $this->driverPool->moveToUnavailable($request->getDriver());

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function dropOff(Ride $ride, DropOffRequest $request)
    {
        $ride->finished($request->getLocation());

        $this->driverPool->moveToAvailable($ride->driver);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
