<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Payment::class;
    public function definition(): array
    {
        $user = User::inRandomOrder()->first(); // Вибір випадкового користувача

        return [
            'user_id' => $user ? $user->id : User::factory(), // Якщо користувач існує, беремо його id, інакше створюємо нового
            'card_number' => $this->faker->creditCardNumber, // Генерація випадкового номера картки
            'expire_date' => $this->faker->creditCardExpirationDateString, // Генерація випадкової дати закінчення дії картки
            'payment_method' => $this->faker->randomElement(['Післяоплата', 'Оплата картою', 'Передплата']), // Випадковий метод оплати
        ];
    }
}
