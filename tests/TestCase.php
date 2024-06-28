<?php

namespace Tests;

use App\Models\Car;
use App\Models\Driver;
use App\ValueObjects\Location;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class TestCase extends BaseTestCase
{
    protected function driverAvailableAt(Driver $driver, Location $location): void
    {
        $car = Car::factory()->create();

        $this->patchJson(
            route('drivers.check-in', ['driver' => $driver->id]),
            [
                'car_id' => $car->id,
            ],
        )
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->patchJson(
            route('drivers.update-current-location', ['driver' => $driver->id]),
            [
                'car_id' => $car->id,
                'longitude' => $location->longitude,
                'latitude' => $location->latitude,
            ],
        )
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
