<?php

namespace App\Jobs;

use App\Enums\DriverStatus;
use App\Models\Ride;
use App\Notifications\RideRequestedNotification;
use App\Services\LocationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class NotifyClosestAvailableDrivers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 4;

    public $backoff = [10, 20, 30];

    public function __construct(private readonly Ride $ride)
    {
    }

    public function handle(LocationService $locationService): void
    {
        $closestDrivers = $locationService->getClosestDrivers($this->ride, DriverStatus::Available);

        $closestDrivers->each->notify(new RideRequestedNotification($this->ride));
    }

    public function failed(?Throwable $exception): void
    {
        NotifyClosestHoldOnDriver::dispatch($this->ride);
    }
}
