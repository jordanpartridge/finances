<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'position_id' => Position::factory(),
            'transaction_type' => 'buy',
            'quantity' => $this->faker->randomFloat(8, 1, 1000),
            'price_per_share' => $this->faker->randomFloat(2, 10, 500),
            'transaction_date' => $this->faker->date(),
            'settlement_date' => $this->faker->date(),
        ];
    }
}
