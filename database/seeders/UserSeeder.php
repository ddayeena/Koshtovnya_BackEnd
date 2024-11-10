<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\WishList;
use App\Models\WishlistProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(10)->create(); 

        foreach ($users as $user) {
            $products = Product::inRandomOrder()->take(2)->get();

            // Створення списку бажаного для кожного користувача
            $wishlist = WishList::create(['user_id' => $user->id]);
            foreach ($products as $product) {
                WishlistProduct::create([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $product->id, 
                ]);
            }

            // Створення кошика для кожного користувача
            $cart = Cart::create(['user_id' => $user->id]);
            foreach ($products as $product) {
                CartProduct::factory()->count(2)->create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id, 
                ]);
            }
        }
    }

}
