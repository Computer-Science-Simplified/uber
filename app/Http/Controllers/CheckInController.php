<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInRequest;
use App\Models\Driver;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class CheckInController extends Controller
{
    public function __invoke(Driver $driver, CheckInRequest $request)
    {
        Redis::sadd('drivers:available', $driver->id);

        Redis::zrem('drivers:unavailable', $driver->id);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
