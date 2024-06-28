<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\Driver;
use App\Services\DriverPoolService;
use App\ValueObjects\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UpdateCurrentLocationTest extends TestCase
{
    use RefreshDatabase;

    private DriverPoolService $driverPool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driverPool = app(DriverPoolService::class);
    }

    protected function tearDown(): void
    {
        Redis::flushall();

        parent::tearDown();
    }

    #[Test]
    public function a_driver_can_update_its_current_location()
    {
        $driver = Driver::factory()->create();

        $car = Car::factory()->create();

        $location = Location::create(1, 1);

        $this->patchJson(
            route('drivers.update-current-location', ['driver' => $driver->id]),
            [
                'car_id' => $car->id,
                'longitude' => $location->longitude,
                'latitude' => $location->latitude,
            ],
        )
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertTrue(!!Redis::zscore('drivers:current-locations', $driver->id));
    }
}
