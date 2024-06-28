<?php

namespace App\Jobs;

use App\Models\Ride;
use App\Notifications\RideRequestedNotification;
use App\Services\LocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyClosestDriver implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Ride $ride)
    {
    }

    public function handle(LocationService $locationService): void
    {
        try {
            $closestDriver = $locationService->getClosestDriver($this->ride);

            $closestDriver->notify(new RideRequestedNotification($this->ride));
        } catch (ModelNotFoundException) {}
    }
}
