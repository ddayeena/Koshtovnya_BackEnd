<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Delivery::class;
    public function definition(): array
    {
        return [
            'post_service' => $this->faker->randomElement(['Укрпошта', 'Нова Пошта']), 
            'city' => $this->faker->city, 
            'post_office' => $this->faker->word(10), 
            'cost' => $this->faker->numberBetween(20, 200), 
        ];
    }
}
