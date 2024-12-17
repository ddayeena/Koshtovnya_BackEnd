<?php

namespace Database\Seeders;

use App\Models\Fitting;
use App\Models\FittingProduct;
use App\Models\Material;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FittingProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fittings = Fitting::all();
        $products = Product::all();
        $material = Material::all();

        foreach ($fittings as $fitting) {
            $uniqueProducts = $products->random(2); 

            foreach ($uniqueProducts as $product) {
                FittingProduct::factory()->create([
                    'material_id'=> $material->id,
                    'fitting_id' => $fitting->id,
                    'product_id' => $product->id,
                    'quantity' => rand(0, 20),
                ]);
            }
        }
    }
}
