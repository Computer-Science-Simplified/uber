<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRideRequest;
use App\Http\Requests\DropOffRequest;
use App\Models\Ride;
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
