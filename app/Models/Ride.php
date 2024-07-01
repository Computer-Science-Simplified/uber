<?php

namespace App\Models;

use App\Enums\RideStatus;
use App\ValueObjects\Location;
use Finite\StatefulInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Ride extends Model implements StatefulInterface
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => RideStatus::class,
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function pickUpLocation(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $point = DB::selectOne("SELECT ST_AsText(pick_up_location) as location FROM rides WHERE id = ?", [$this->id]);

                if ($point && isset($point->location)) {
                    preg_match('/POINT\(([-\d\.]+) ([-\d\.]+)\)/', $point->location, $matches);

                    return Location::create((float) $matches[1], (float) $matches[2]);
                }
            },
            set: fn (Location $value) => DB::raw("ST_GeomFromText('POINT({$value->longitude} {$value->latitude})')"),
        );
    }

    public function dropOffLocation(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $point = DB::selectOne("SELECT ST_AsText(drop_off_location) as location FROM rides WHERE id = ?", [$this->id]);

                if ($point && isset($point->location)) {
                    preg_match('/POINT\(([-\d\.]+) ([-\d\.]+)\)/', $point->location, $matches);

                    return Location::create((float) $matches[1], (float) $matches[2]);
                }
            },
            set: function (?Location $value) {
                if ($value) {
                    return DB::raw("ST_GeomFromText('POINT({$value->longitude} {$value->latitude})')");
                }
            },
        );
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public static function createWaiting(User $user, Location $pickUpLocation): self
    {
        return Ride::create([
            'status' => RideStatus::Waiting,
            'user_id' => $user->id,
            'pick_up_location' => $pickUpLocation,
        ]);
    }

    public function accepted(Driver $driver, Car $car): void
    {
        $this->update([
            'status' => RideStatus::Accepted,
            'driver_id' => $driver->id,
            'car_id' => $car->id,
            'accepted_at' => now(),
        ]);
    }

    public function inProgress(): void
    {
        $this->update([
            'status' => RideStatus::InProgress,
            'started_at' => now(),
        ]);
    }

    public function finished(Location $dropOffLocation): void
    {
        $this->update([
            'status' => RideStatus::Finished,
            'finished_at' => now(),
            'drop_off_location' => $dropOffLocation,
        ]);
    }

    public function getFiniteState()
    {
        return $this->status?->value;
    }

    public function setFiniteState($state)
    {
        $this->status = RideStatus::from($state);
    }
}
