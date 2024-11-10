<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\ProductColor;
use App\Models\ProductDescription;
use Illuminate\Database\Seeder;

class ProductColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = Color::factory()->count(10)->create();
        $productDescriptions = ProductDescription::all();

        foreach ($colors as $color) {
            // Унікальні продукти для кожного кольору
            $uniqueProducts = $productDescriptions->random(3); 

            foreach ($uniqueProducts as $product) {
                ProductColor::create([
                    'color_id' => $color->id,
                    'product_description_id' => $product->id,
                ]);
            }
        }
    }
}
