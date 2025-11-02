<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bid = $this->faker->randomFloat(2, 10, 500);
        $ask = $bid + 0.05;

        return [
            'ticker' => $this->faker->bothify('???'),
            'bid' => $bid,
            'ask' => $ask,
            'last' => ($bid + $ask) / 2,
            'quoted_at' => $this->faker->dateTime(),
        ];
    }
}
