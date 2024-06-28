<?php

namespace Tests\Feature;

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

class GetClosestDriverTest extends TestCase
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

        // Budapest, Szechenyi Rakpart
        $pickUpLocation = Location::create(47.5097778, 19.0460277);

        $data = $this->postJson(route('rides.store'), [
            'longitude' => $pickUpLocation->longitude,
            'latitude' => $pickUpLocation->latitude,
        ])
            ->assertStatus(Response::HTTP_CREATED)
            ->json('data');

        $ride = Ride::find($data['id']);

        $closestDriver = Driver::factory()->create();

        // Budapest, Antall Jozsef Rakpart
        $closestDriverLocation = Location::create(47.513951, 19.046571);

        $this->driverAvailableAt($closestDriver, $closestDriverLocation);

        $otherDriver = Driver::factory()->create();

        // Budapest, Robert Karoly krt.
        $otherDriverLocation = Location::create(47.520875, 19.085512);

        $this->driverAvailableAt($otherDriver, $otherDriverLocation);

        $driver = $this->locationService->getClosestDriver($ride);

        $this->assertSame($closestDriver->id, $driver->id);
    }

    #[Test]
    public function it_should_return_the_closest_available_driver()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Budapest, Szechenyi Rakpart
        $pickUpLocation = Location::create(47.5097778, 19.0460277);

        $data = $this->postJson(route('rides.store'), [
            'longitude' => $pickUpLocation->longitude,
            'latitude' => $pickUpLocation->latitude,
        ])
            ->assertStatus(Response::HTTP_CREATED)
            ->json('data');

        $ride = Ride::find($data['id']);

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

        $this->driverPool->markAsUnavailable($closestDriverNotAvailable);

        $this->driverPool->markAsOnHold($nearByDriverOnHold);

        $driver = $this->locationService->getClosestDriver($ride);

        $this->assertSame($otherDriverAvailable->id, $driver->id);
    }
}
