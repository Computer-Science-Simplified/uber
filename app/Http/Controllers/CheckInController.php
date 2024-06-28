<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckInRequest;
use App\Models\Driver;
use App\Services\DriverPoolService;
use Symfony\Component\HttpFoundation\Response;

class CheckInController extends Controller
{
    public function __invoke(Driver $driver, CheckInRequest $request, DriverPoolService $driverPool)
    {
        $driverPool->markAsAvailable($driver);

        return response('', Response::HTTP_NO_CONTENT);
    }
}
