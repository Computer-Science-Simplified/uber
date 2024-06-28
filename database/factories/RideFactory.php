<?php

namespace Database\Factories;

use App\Enums\RideStatus;
use App\Models\Car;
use App\Models\Driver;
use App\Models\User;
use App\ValueObjects\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ride>
 */
class RideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'driver_id' => Driver::factory(),
            'user_id' => User::factory(),
            'status' => RideStatus::Waiting,
            'accepted_at' => now()->subMinutes(20),
            'started_at' => null,
            'finished_at' => null,
            'pick_up_location' => Location::create(19.0460277, 47.5097778),
            'drop_off_location' => null,
        ];
    }
}
