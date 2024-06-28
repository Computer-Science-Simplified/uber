<?php

namespace Tests\Feature;

use App\Enums\RideStatus;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
