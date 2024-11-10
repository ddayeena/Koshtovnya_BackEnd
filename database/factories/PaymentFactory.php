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
        return [
            'card_number' => $this->faker->creditCardNumber,
            'expire_date' => $this->faker->creditCardExpirationDateString, 
            'payment_method' => $this->faker->randomElement(['Післяоплата', 'Оплата картою', 'Передплата']), 
            'transaction_number' => $this->faker->uuid()
            
        ];
    }
}
