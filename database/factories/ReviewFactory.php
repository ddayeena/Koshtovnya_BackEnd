<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id, // Вибираємо випадковий cart_id
            'product_id' => Product::inRandomOrder()->first()->id, // Вибираємо випадковий product_id
            'comment' => $this->faker->paragraph(),
            'rating' => $this->faker->numberBetween(1, 5),
        ];
    }
}
