<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'manufacturer' => $this->faker->words(3, true),
            'type' => $this->faker->words(3, true),
            'license_plate' => chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . '-' . rand(1, 9) . rand(1, 9) . rand(1, 9),
        ];
    }
}
