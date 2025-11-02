<?php

namespace Database\Factories;

use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "ticker" => $this->faker->unique()->company(),
            "shares" => $this->faker->numberBetween(1, 1000),
            "portfolio_id" => Portfolio::factory(),
        ];
    }
}
