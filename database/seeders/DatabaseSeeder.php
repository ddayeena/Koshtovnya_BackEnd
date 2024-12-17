<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

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
            ColorProductSeeder::class,
            ProductVariantSeeder::class,
            MaterialSeeder::class,
            FittingSeeder::class,
            FittingProductSeeder::class,
            ExpenseSeeder::class,
            UserSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            NotificationSeeder::class,
            DeliveryTypeSeeder::class,
        ]);
    }
}
