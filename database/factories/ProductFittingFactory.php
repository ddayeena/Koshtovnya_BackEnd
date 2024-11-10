<?php

namespace Database\Factories;

use App\Models\Fitting;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductFitting>
 */
class ProductFittingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [   
            'quantity' => $this->faker->numberBetween(1,50),
        ];
    }
}
