<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCurrentLocationRequest;
use App\Models\Driver;
use App\Services\LocationService;
use Symfony\Component\HttpFoundation\Response;

class UpdateCurrentLocationController extends Controller
{
    public function __invoke(Driver $driver, UpdateCurrentLocationRequest $request, LocationService $locationService)
    {
        $locationService->updateCurrentLocation($driver, $request->getLocation());

        return response('', Response::HTTP_NO_CONTENT);
    }
}
