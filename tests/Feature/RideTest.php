<?php

namespace Tests\Feature;

use App\Enums\RideStatus;
use App\Models\Car;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\User;
use App\Notifications\RideRequestedNotification;
use App\ValueObjects\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RideTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        parent::tearDown();

//        Redis::flushall();
    }

    #[Test]
    public function a_user_can_request_a_new_ride()
    {
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

    private function driverAvailableAt(Driver $driver, Location $location): void
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
