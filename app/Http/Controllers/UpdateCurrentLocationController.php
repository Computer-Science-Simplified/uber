<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCurrentLocationRequest;
use App\Models\Driver;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class UpdateCurrentLocationController extends Controller
{
    public function __invoke(Driver $driver, UpdateCurrentLocationRequest $request)
    {
        $location = $request->getLocation();

        Redis::geoadd('drivers:current-locations', $location->longitude, $location->latitude, $driver->id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
