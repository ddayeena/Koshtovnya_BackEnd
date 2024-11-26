<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        foreach ($users as $user) {
            $productsForUser = $products->random(2); 
            foreach ($productsForUser as $product) {
                Notification::firstOrCreate([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
