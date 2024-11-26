<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\ColorProduct;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ColorProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = Color::factory()->count(10)->create();
        $products = Product::all();

        foreach ($colors as $color) {
            // Унікальні продукти для кожного кольору
            $uniqueProducts = $products->random(3); 

            foreach ($uniqueProducts as $product) {
                ColorProduct::create([
                    'color_id' => $color->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
