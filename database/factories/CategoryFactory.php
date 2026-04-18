<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
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
            'name' => fake()->word() . ' ' . fake()->randomNumber(5),
            'icon' => fake()->imageUrl(64, 64, 'finance', true),
            'type' => fake()->randomElement(['income', 'expense']),
        ];
    }
}
