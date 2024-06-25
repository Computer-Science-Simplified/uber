<?php

use App\Enums\RideStatus;
use App\Models\Car;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Car::class)->constrained();
            $table->foreignIdFor(Driver::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('status')->default(RideStatus::Waiting);
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->geography('pick_up_location', 'point', 0);
            $table->geography('drop_off_location', 'point', 0)->nullable();
            $table->timestamps();

            $table->spatialIndex('pick_up_location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
