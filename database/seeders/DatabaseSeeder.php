<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductDescription;
use App\Models\ProductSize;
use App\Models\Size;
use App\Models\User;
use App\Models\WishList;
use App\Models\WishlistProduct;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB as FacadesDB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        $categories = Category::factory(6)->create();

        // Створюємо описи продуктів із випадковими категоріями
        $productDescriptions = collect(range(1, 10))->map(function () use ($categories) {
            return ProductDescription::factory()->create([
                'category_id' => $categories->random()->id,
            ]);
        });
        
        // Створюємо продукти, зв'язані з описами
        foreach ($productDescriptions as $productDescription) {
            Product::factory()->create([
                'product_description_id' => $productDescription->id,
            ]);
        }
        

        $sizes = Size::factory(10)->create(); 
        $colors = Color::factory(10)->create(); 
        
        // Генеруємо product_sizes з унікальними комбінаціями
        foreach ($productDescriptions as $productDescription) {
            ProductSize::factory()->create([
                'product_description_id' => $productDescription->id,
                'size_id' => $sizes->random()->id,
            ]);
        }
        
        // Генеруємо product_colors з унікальними комбінаціями
        foreach ($productDescriptions as $productDescription) {
            ProductColor::factory()->create([
                'product_description_id' => $productDescription->id,
                'color_id' => $colors->random()->id,
            ]);
        }



        User::factory()->count(10)->create()->each(function ($user) {
            // Створення списків бажань для кожного користувача
            $wishlist = WishList::factory()->create(['user_id' => $user->id]);
            // Створення продуктів у списку бажань
            WishlistProduct::factory()->count(5)->create(['wishlist_id' => $wishlist->id]);

            // Створення кошиків для кожного користувача
            $cart = Cart::factory()->create(['user_id' => $user->id]);
            // Створення продуктів у кошику
            CartProduct::factory()->count(3)->create(['cart_id' => $cart->id]);

            // Створення доставок для кожного користувача
            $delivery = Delivery::factory()->create(['user_id' => $user->id]);

            // Створення платежів для кожного користувача
            $payment = Payment::factory()->create(['user_id' => $user->id]);

            // Створення замовлень для кожного користувача
            $order = Order::factory()->create([
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'delivery_address_id' => $delivery->id,
            ]);
            // Створення продуктів у замовленні
            OrderProduct::factory()->count(2)->create(['order_id' => $order->id]);


            DB::table('site_settings')->insertOrIgnore([
                ['setting_key' => 'site_logo', 'setting_value' => 'logo.png'],
                ['setting_key' => 'footer_email_info', 'setting_value' => 'koshtovnya@gmail.com'],
                ['setting_key' => 'footer_address_info', 'setting_value' => 'вул. Степана Бандери 22, Коломия'],
                ['setting_key' => 'footer_phone_number', 'setting_value' => '+380123456789'],
            ]);
            
        });
    }
}
