<?php

namespace Database\Factories;

use App\Models\Color;
use App\Models\ProductColor;
use App\Models\ProductDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductColor>
 */
class ProductColorFactory extends Factory
{
    protected $model = ProductColor::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_description_id' => ProductDescription::inRandomOrder()->first()->id, 
            'color_id' => Color::inRandomOrder()->first()->id,        
        ];
    }       
}
