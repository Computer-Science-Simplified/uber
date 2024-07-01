<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCurrentLocationRequest;
use App\Models\Driver;
use App\Services\DriverPoolService;
use App\Services\LocationService;
use Symfony\Component\HttpFoundation\Response;

class DriverController extends Controller
{
    public function checkIn(Driver $driver, DriverPoolService $driverPool)
    {
        $driverPool->moveToAvailable($driver);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function checkOut(Driver $driver, DriverPoolService $driverPool)
    {
        $driverPool->remove($driver);

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function updateCurrentLocation(Driver $driver, UpdateCurrentLocationRequest $request, LocationService $locationService)
    {
        $locationService->updateCurrentLocation($driver, $request->getLocation());

        return response('', Response::HTTP_NO_CONTENT);
    }
}
