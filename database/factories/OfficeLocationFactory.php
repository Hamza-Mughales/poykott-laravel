<?php

namespace Database\Factories;

use App\Models\OfficeLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfficeLocation>
 */
class OfficeLocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OfficeLocation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'lat' => fake()->latitude(),
            'lng' => fake()->longitude(),
        ];
    }
}
