<?php

namespace Tests\Feature;

use App\Enums\DriverStatus;
use App\Models\Car;
use App\Models\Driver;
use App\Services\DriverPoolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CheckInTest extends TestCase
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
    public function a_driver_should_be_able_to_check_in()
    {
        $driver = Driver::factory()->create();

        $this->driverPool->markAsUnavailable($driver);

        $this->assertSame(DriverStatus::Unavailable, $this->driverPool->getStatus($driver));

        $car = Car::factory()->create();

        $this->patchJson(
            route('drivers.check-in', ['driver' => $driver->id]),
            [
                'car_id' => $car->id,
            ],
        )
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSame(DriverStatus::Available, $this->driverPool->getStatus($driver));
    }
}
