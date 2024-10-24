<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderProduct>
 */
class OrderProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = OrderProduct::class;
    public function definition(): array
    {
        return [
            'order_id' => Order::inRandomOrder()->first()->id, // Випадковий ідентифікатор замовлення
            'product_id' => Product::inRandomOrder()->first()->id, // Випадковий ідентифікатор продукту
        ];
    }
}
