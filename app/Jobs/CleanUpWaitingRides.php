<?php

namespace App\Jobs;

use App\Enums\RideStatus;
use App\Models\Ride;
use App\Notifications\PassangerWaitingForYouNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CleanUpWaitingRides implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $waitingRidesWithDrivers = Ride::query()
            ->with('driver')
            ->where('status', RideStatus::Waiting)
            ->whereNotNull('driver_id')
            ->where('accepted_at', '<', now()->subMinutes(30))
            ->where('reminder_sent', false)
            ->get();

        foreach ($waitingRidesWithDrivers as $ride) {
            $ride->driver->notify(new PassangerWaitingForYouNotification());
        }

        DB::table('rides')
            ->whereIn('id', $waitingRidesWithDrivers->pluck('id'))
            ->update([
                'reminder_sent' => true,
            ]);

        DB::table('rides')
            ->where('status', RideStatus::Waiting)
            ->whereNull('driver_id')
            ->where('created_at', '<', now()->subMinutes(10))
            ->update([
                'status' => RideStatus::Abandoned,
            ]);
    }
}
