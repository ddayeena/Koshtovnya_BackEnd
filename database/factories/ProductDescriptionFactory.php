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
            'weight' => $this->faker->numberBetween(5, 400), 
            'bead_producer_id' => BeadProducer::inRandomOrder()->first()->id,
            'country_of_manufacture' => $this->faker->country(),
            'type_of_bead' => $this->faker->randomElement(['Матовий', 'Не матовий']),
            'category_id' => Category::inRandomOrder()->first()->id
        ];
    }
}
