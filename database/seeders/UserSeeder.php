<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Product;
use App\Models\ProductWishList;
use App\Models\User;
use App\Models\WishList;
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
                ProductWishList::firstOrCreate([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $product->id, 
                ]);
            }

            // Створення кошика для кожного користувача
            $cart = Cart::create(['user_id' => $user->id]);
            foreach ($products as $product) {
                // Перевірка на наявність продукту в кошику, якщо є — збільшення кількості
                $cartProduct = CartProduct::where('cart_id', $cart->id)
                                          ->where('product_id', $product->id)
                                          ->first();
                
                if ($cartProduct) {
                    // Якщо запис є, збільшуємо кількість
                    $cartProduct->increment('quantity', 2);  // Збільшуємо кількість на 2
                } else {
                    // Якщо запису немає, створюємо новий
                    CartProduct::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ]);
                }
            }
        }
    }

}
