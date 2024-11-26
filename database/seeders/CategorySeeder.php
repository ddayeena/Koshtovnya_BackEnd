<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Category::factory(6)->create();
        DB::table('categories')->insertOrIgnore([
            ['name' => 'Браслети','image_url'=>'https://i.pinimg.com/564x/7b/60/e3/7b60e35f17cba3c99bd03aaa92927084.jpg'],
            ['name' => 'Гердани','image_url'=>'https://ourspeople.com.ua/image/cache/catalog/Seller_100/0002-900x900.png'],
            ['name' => 'Дукати','image_url'=>'https://cdn01.pinkoi.com/product/tU7xqhXK/16/800x0.avif'],
            ['name' => 'Сережки','image_url'=>'https://e-c.storage.googleapis.com/res/8f43e6da-57a8-4181-88b0-c69febe7c2e7/original'],
            ['name' => 'Силянки','image_url'=>'https://etnoxata.com.ua/image/cache/catalog/470/470-5511_01-300x450.JPG'],
            ['name' => 'Пояси','image_url'=>'https://ds1.skrami.com/products/p966873_5cefc13784773.jpg'],
        ]);
    }
}
