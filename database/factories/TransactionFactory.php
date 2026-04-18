<?php

namespace Database\Factories;

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
            'user_id' => rand(1, 2), 
            'category_id' => rand(1, 10), 
            'amount' => fake()->randomFloat(2, 10, 20000),
            'transaction_type' => fake()->randomElement(['income', 'expense']),
            'transaction_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'notes' => fake()->sentence(),
        ];
    }
}
