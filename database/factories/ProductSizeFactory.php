<?php

namespace Database\Factories;

use App\Models\ProductDescription;
use App\Models\ProductSize;
use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductSize>
 */
class ProductSizeFactory extends Factory
{
    protected $model = ProductSize::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_description_id' => ProductDescription::inRandomOrder()->first()->id, 
            'size_id' => Size::inRandomOrder()->first()->id,                
        ];
    }
}
