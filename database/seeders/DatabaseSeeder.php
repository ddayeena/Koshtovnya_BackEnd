<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\BeadProducer;
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
use App\Models\Review;
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


        $this->call([
            SiteSettingSeeder::class,
            CategorySeeder::class,
            BeadProducerSeeder::class,
            ProductSeeder::class,
            ProductColorSeeder::class,
            ProductSizeSeeder::class,
            FittingSeeder::class,
            ProductFittingSeeder::class,
            ExpenseSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            NotificationSeeder::class,
        ]);




        // User::factory()->count(10)->create()->each(function ($user) use ($productDescriptions) {
        
        //     // Створення відгуків для кожного користувача
        //     $productDescriptions->random(3)->each(function ($productDescription) use ($user) {
        //         Review::factory()->create([
        //             'user_id' => $user->id,
        //             'product_id' => $productDescription->id, // передбачаючи, що product_id буде братися з опису
        //         ]);
        //     });
        
          
        // });
    }
}
