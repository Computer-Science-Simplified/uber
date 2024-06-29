<?php

namespace Feature;

use App\Enums\DriverStatus;
use App\Models\Driver;
use App\Services\DriverPoolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CheckOutTest extends TestCase
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
    #[DataProvider('statusProvider')]
    public function a_driver_should_be_able_to_check_out(DriverStatus $status)
    {
        $driver = Driver::factory()->create();

        switch ($status) {
            case DriverStatus::Available:
                $this->driverPool->markAsAvailable($driver);
                break;
            case DriverStatus::Unavailable:
                $this->driverPool->markAsUnavailable($driver);
                break;
            case DriverStatus::OnHold:
                $this->driverPool->markAsOnHold($driver);
                break;
        }

        $this->assertSame($status, $this->driverPool->getStatus($driver));

        $this->patchJson(route('drivers.check-out', ['driver' => $driver->id]))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSame(DriverStatus::NotInPool, $this->driverPool->getStatus($driver));
    }

    public static function statusProvider()
    {
        return [
            [DriverStatus::Available],
            [DriverStatus::Unavailable],
            [DriverStatus::OnHold],
        ];
    }
}
