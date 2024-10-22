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
    }
}
