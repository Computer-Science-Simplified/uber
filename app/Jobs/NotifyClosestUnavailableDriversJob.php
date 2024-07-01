<?php

namespace App\Jobs;

use App\Enums\DriverStatus;
use App\Models\Ride;
use App\Notifications\RideRequestedNotification;
use App\Services\LocationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyClosestUnavailableDriversJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 4;

    public $backoff = [10, 20, 30];

    public function __construct(private readonly Ride $ride)
    {
    }

    public function handle(LocationService $locationService): void
    {
        $drivers = $locationService->getClosestDrivers(
            $this->ride->pick_up_location,
            DriverStatus::Unavailable,
            10,
        );

        if ($drivers->isEmpty()) {
            throw new Exception('No unavailable drivers');
        }

        $drivers->each->notify(new RideRequestedNotification($this->ride));
    }
}