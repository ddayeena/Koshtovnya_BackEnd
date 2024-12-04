<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $products = Product::inRandomOrder()->take(2)->get();


            $order = Order::factory()->create([
                'user_id' => $user->id,
            ]);

            $delivery = Delivery::factory()->create(['order_id' => $order->id]);
            $payment = Payment::factory()->create(['order_id' => $order->id]);

            foreach ($products as $product) {
                OrderProduct::factory()->count(2)->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id, 

                ]);
            }
        }
    }
}
