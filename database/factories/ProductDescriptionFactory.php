<?php

namespace Database\Factories;

use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\ProductDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductDescription>
 */
class ProductDescriptionFactory extends Factory
{
    protected $model = ProductDescription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'material' => $this->faker->word(),
            'weight' => $this->faker->randomFloat(2, 0, 100), 
            // 'bead_manufacturer' => $this->faker->company(),
            'bead_producer_id' => BeadProducer::get()->random()->id,
            'country_of_manufacture' => $this->faker->country(),
            'type_of_bead' => $this->faker->randomElement(['Матовий', 'Не матовий']),
            'category_id' => Category::get()->random()->id 
        ];
    }
}
