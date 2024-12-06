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
            'type_of_card' => $this->faker->name(),
            'payment_method' => $this->faker->randomElement(['Післяоплата', 'Оплата картою', 'Передплата']), 
            'transaction_number' => $this->faker->uuid(),
            'amount' => $this->faker->numberBetween(200, 2000), 
            
        ];
    }
}
