<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ProductVariant;
use App\Models\Size;
use Carbon\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $products = Product::all();

        foreach ($products as $product) {
            $sizes = collect();
            while ($sizes->count() < 3) {
                $randomSize = rand(20, 110); 
                $sizes->push($randomSize);
                $sizes = $sizes->unique();
            }

            foreach ($sizes as $size) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'quantity' => rand(0, 20),
                ]);
            }
        }
    }
}
