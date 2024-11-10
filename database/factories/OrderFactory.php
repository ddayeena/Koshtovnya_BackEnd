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
            'order_date' => $this->faker->dateTime, 
            'status' => $this->faker->randomElement(['В очікуванні', 'Відправлено', 'Доставлено']),
            'total_amount' => $this->faker->randomFloat(2, 100, 10000), 
        ];
    }
}
