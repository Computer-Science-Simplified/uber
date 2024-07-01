<?php

namespace Tests\Feature;

use App\Enums\DriverStatus;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\User;
use App\Services\DriverPoolService;
use App\Services\LocationService;
use App\ValueObjects\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetClosestDriversTest extends TestCase
{
    use RefreshDatabase;

    private DriverPoolService $driverPool;
    private LocationService $locationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driverPool = app(DriverPoolService::class);

        $this->locationService = app(LocationService::class);
    }

    protected function tearDown(): void
    {
        Redis::flushall();

        parent::tearDown();
    }

    #[Test]
    public function it_should_return_the_closest_driver()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $ride = Ride::factory()->create([
            'user_id' => $user->id,
            // Budapest, Szechenyi Rakpart
            'pick_up_location' => Location::create(47.5097778, 19.0460277),
        ]);

        $closestDriver = Driver::factory()->create();

        // Budapest, Antall Jozsef Rakpart
        $closestDriverLocation = Location::create(47.513951, 19.046571);

        $this->driverAvailableAt($closestDriver, $closestDriverLocation);

        $otherDriver = Driver::factory()->create();

        // Budapest, Robert Karoly krt.
        $otherDriverLocation = Location::create(47.520875, 19.085512);

        $this->driverAvailableAt($otherDriver, $otherDriverLocation);

        $driver = $this->locationService->getClosestDrivers($ride->pick_up_location, DriverStatus::Available)->first();

        $this->assertSame($closestDriver->id, $driver->id);
    }

    #[Test]
    public function it_should_return_the_closest_available_driver()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $ride = Ride::factory()->create([
            'user_id' => $user->id,
            // Budapest, Szechenyi Rakpart
            'pick_up_location' => Location::create(47.5097778, 19.0460277),
        ]);

        $closestDriverNotAvailable = Driver::factory()->create();

        // Budapest, Antall Jozsef Rakpart
        $closestDriverLocation = Location::create(47.513951, 19.046571);

        $this->driverAvailableAt($closestDriverNotAvailable, $closestDriverLocation);

        $nearByDriverOnHold = Driver::factory()->create();

        // Budapest, Szent Istvan krt
        $nearByDriverOnHoldLocation = Location::create(47.511655, 19.052496);

        $this->driverAvailableAt($nearByDriverOnHold, $nearByDriverOnHoldLocation);

        $otherDriverAvailable = Driver::factory()->create();

        // Budapest, Robert Karoly krt.
        $otherDriverLocation = Location::create(47.520875, 19.085512);

        $this->driverAvailableAt($otherDriverAvailable, $otherDriverLocation);

        $this->driverPool->moveToUnavailable($closestDriverNotAvailable);

        $this->driverPool->moveToOnHold($nearByDriverOnHold);

        $driver = $this->locationService->getClosestDrivers($ride->pick_up_location, DriverStatus::Available)->first();

        $this->assertSame($otherDriverAvailable->id, $driver->id);
    }
}
