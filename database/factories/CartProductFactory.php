<?php

namespace Database\Factories;

use App\Models\CartProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartProduct>
 */
class CartProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = CartProduct::class;
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(0,30),

        ];
    }
}
