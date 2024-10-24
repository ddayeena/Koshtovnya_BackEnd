<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\WishList;
use App\Models\WishlistProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WishlistProduct>
 */
class WishlistProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = WishlistProduct::class;
    public function definition(): array
    {
        return [
            'wishlist_id' => WishList::all()->random()->id, // Вибираємо випадковий wishlist_id
            'product_id' => Product::all()->random()->id,   // Вибираємо випадковий product_id
        ];
    }
}
