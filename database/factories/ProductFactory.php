<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' ' . $this->faker->word(), 
            'price' => $this->faker->randomFloat(2, 1, 1000), 
            'image_url' => $this->faker->imageUrl(640, 480, 'products', true), 
            'product_description_id' => ProductDescription::get()->random()->id,
        ];
    }
}
