<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartProduct>
 */
class CartProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = CartProduct::class;
    public function definition(): array
    {
        return [
            'cart_id' => Cart::inRandomOrder()->first()->id, // Вибираємо випадковий cart_id
            'product_id' => Product::inRandomOrder()->first()->id, // Вибираємо випадковий product_id
        ];
    }
}
