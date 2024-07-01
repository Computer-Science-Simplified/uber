<?php

namespace Tests\Feature;

use App\Enums\DriverStatus;
use App\Enums\RideStatus;
use App\Jobs\NotifyClosestAvailableDriversJob;
use App\Models\Car;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\User;
use App\Notifications\RideRequestedNotification;
use App\Services\DriverPoolService;
use App\ValueObjects\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RideTest extends TestCase
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
    public function a_user_can_request_a_new_ride()
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user);

        $data = $this->postJson(route('rides.store'), [
            'longitude' => 1,
            'latitude' => 1,
        ])
            ->assertStatus(Response::HTTP_CREATED)
            ->json('data');

        $this->assertDatabaseHas('rides', [
            'id' => $data['id'],
            'status' => RideStatus::Waiting,
            'driver_id' => null,
            'car_id' => null,
        ]);

        Queue::assertPushed(NotifyClosestAvailableDriversJob::class);
    }

    #[Test]
    public function the_closest_driver_gets_a_notification_when_a_user_requests_a_ride()
    {
        Notification::fake();

        $driver = Driver::factory()->create();

        $this->driverAvailableAt($driver, Location::create(1, 1));

        $user = User::factory()->create();

        $this->actingAs($user);

        $data = $this->postJson(route('rides.store'), [
            'longitude' => 1,
            'latitude' => 1,
        ])
            ->assertStatus(Response::HTTP_CREATED)
            ->json('data');

        $ride = Ride::find($data['id']);

        Notification::assertSentTo($driver, RideRequestedNotification::class, function (RideRequestedNotification $notification) use ($ride) {
            $this->assertEquals($ride->id, $notification->ride->id);

            return true;
        });
    }

    #[Test]
    public function a_driver_can_accept_a_ride()
    {
        $ride = Ride::factory()->create([
            'status' => RideStatus::Waiting,
        ]);

        $car = Car::factory()->create();

        $driver = Driver::factory()->create();

        $this->travelTo('2024-12-01 15:00:00', function () use ($ride, $car, $driver) {
            $this->patchJson(
                route('rides.accept', ['ride' => $ride->id]),
                [
                    'driver_id' => $driver->id,
                    'car_id' => $car->id,
                ],
            )
                ->assertStatus(Response::HTTP_NO_CONTENT);

            $this->assertDatabaseHas('rides', [
                'id' => $ride->id,
                'status' => RideStatus::Accepted,
                'driver_id' => $driver->id,
                'car_id' => $car->id,
                'accepted_at' => now(),
            ]);
        });
    }

    #[Test]
    public function a_driver_should_be_on_hold_after_approving_a_ride()
    {
        $ride = Ride::factory()->create([
            'status' => RideStatus::Waiting,
        ]);

        $car = Car::factory()->create();

        $driver = Driver::factory()->create();

        $this->patchJson(
            route('rides.accept', ['ride' => $ride->id]),
            [
                'driver_id' => $driver->id,
                'car_id' => $car->id,
            ],
        )
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSame(DriverStatus::OnHold, $this->driverPool->getStatus($driver));
    }

    #[Test]
    public function a_driver_can_pickup_a_passenger()
    {
        $driver = Driver::factory()->create();

        $ride = Ride::factory()->create([
            'status' => RideStatus::Waiting,
            'driver_id' => $driver->id,
        ]);

        $this->driverAvailableAt($driver, Location::create(1, 1));

        $this->travelTo('2024-12-01 15:00:00', function () use ($ride, $driver) {
            $this->patchJson(
                route('rides.pick-up', ['ride' => $ride->id]),
                [
                    'driver_id' => $driver->id,
                ],
            )
                ->assertStatus(Response::HTTP_NO_CONTENT);

            $this->assertDatabaseHas('rides', [
                'id' => $ride->id,
                'status' => RideStatus::InProgress,
                'driver_id' => $driver->id,
                'started_at' => now(),
            ]);
        });
    }

    #[Test]
    public function a_driver_should_become_unavailable_after_a_pickup()
    {
        $driver = Driver::factory()->create();

        $ride = Ride::factory()->create([
            'status' => RideStatus::Waiting,
            'driver_id' => $driver->id,
        ]);

        $this->driverAvailableAt($driver, Location::create(1, 1));

        $this->patchJson(
            route('rides.pick-up', ['ride' => $ride->id]),
            [
                'driver_id' => $driver->id,
            ],
        )
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSame(DriverStatus::Unavailable, $this->driverPool->getStatus($driver));
    }

    #[Test]
    public function a_driver_can_drop_off_a_passenger()
    {
        $driver = Driver::factory()->create();

        $ride = Ride::factory()->create([
            'status' => RideStatus::InProgress,
            'driver_id' => $driver->id,
        ]);

        $this->driverPool->markAsUnavailable($driver);

        $this->travelTo('2024-12-01 15:00:00', function () use ($ride, $driver) {
            $this->patchJson(
                route('rides.drop-off', ['ride' => $ride->id]),
                [
                    'longitude' => 2,
                    'latitude' => 2,
                ],
            )
                ->assertStatus(Response::HTTP_NO_CONTENT);

            $this->assertDatabaseHas('rides', [
                'id' => $ride->id,
                'status' => RideStatus::Finished,
                'driver_id' => $driver->id,
                'finished_at' => now(),
            ]);
        });
    }

    #[Test]
    public function a_driver_should_become_available_after_a_drop_off()
    {
        $driver = Driver::factory()->create();

        $ride = Ride::factory()->create([
            'status' => RideStatus::InProgress,
            'driver_id' => $driver->id,
        ]);

        $this->driverPool->markAsUnavailable($driver);

        $this->patchJson(
            route('rides.drop-off', ['ride' => $ride->id]),
            [
                'longitude' => 2,
                'latitude' => 2,
            ],
        )
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSame(DriverStatus::Available, $this->driverPool->getStatus($driver));
    }
}
