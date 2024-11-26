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
        $user = User::inRandomOrder()->first(); // Вибір випадкового користувача

        return [
            'city' => $this->faker->city, 
            'post_service' => $this->faker->randomElement(['Укрпошта', 'Нова Пошта']), 
            'post_office' => $this->faker->word(10), 
            'phone_number' => $this->faker->phoneNumber, 
        ];
    }
}
