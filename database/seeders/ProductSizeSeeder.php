<?php

namespace Database\Seeders;

use App\Models\ProductDescription;
use App\Models\ProductSize;
use App\Models\Size;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = Size::factory()->count(10)->create();
        $productDescriptions = ProductDescription::all();

        foreach ($sizes as $size) {
            // Унікальні продукти для кожного розміру
            $uniqueProducts = $productDescriptions->random(3); 

            foreach ($uniqueProducts as $product) {
                ProductSize::create([
                    'size_id' => $size->id,
                    'product_description_id' => $product->id,
                ]);
            }
        }
    }
}
