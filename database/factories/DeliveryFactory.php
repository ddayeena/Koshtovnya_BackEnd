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
            'user_id' => $user ? $user->id : User::factory(), // Якщо користувач існує, беремо його id, інакше створюємо нового
            'city' => $this->faker->city, // Генерація випадкового міста
            'post_service' => $this->faker->randomElement(['Укрпошта', 'Нова Пошта']), // Випадкова служба доставки
            'post_office' => $this->faker->word(10), // Генерація випадкового номера поштового відділення
            'phone_number' => $this->faker->phoneNumber, // Генерація випадкового номера телефону
        ];
    }
}
