<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Order::class;
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id, // Випадковий користувач
            'order_date' => $this->faker->dateTime, // Генерація випадкової дати
            'payment_id' => Payment::inRandomOrder()->first()->id, // Випадковий платіж
            'delivery_address_id' => Delivery::inRandomOrder()->first()->id, // Випадкова адреса доставки
            'status' => $this->faker->randomElement(['В очікуванні', 'Відправлено', 'Доставлено']), // Випадковий статус
            'total_amount' => $this->faker->randomFloat(2, 100, 10000), // Генерація випадкової суми
        ];
    }
}
