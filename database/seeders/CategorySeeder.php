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
        DB::table('categories')->insertOrIgnore([
            ['name' => 'Браслети', 'image_url' => 'https://res.cloudinary.com/dbmnxdhpp/image/upload/v1734437051/categories/cq35ns3nj9qoc9nlcdzv.jpg', 'image_public_id' => 'categories/cq35ns3nj9qoc9nlcdzv'],
            ['name' => 'Гердани', 'image_url' => 'https://res.cloudinary.com/dbmnxdhpp/image/upload/v1734437181/categories/rbdscgz8p2k0ne8m9qep.jpg', 'image_public_id' => 'categories/rbdscgz8p2k0ne8m9qep'],
            ['name' => 'Дукати', 'image_url' => 'https://res.cloudinary.com/dbmnxdhpp/image/upload/v1734437181/categories/rbdscgz8p2k0ne8m9qep.jpg', 'image_public_id' => 'categories/mwhccji0p0sj19o5rgrn'],
            ['name' => 'Сережки', 'image_url' => 'https://res.cloudinary.com/dbmnxdhpp/image/upload/v1734437348/categories/trsyvapax79suz6yoymy.jpg', 'image_public_id' => 'categories/trsyvapax79suz6yoymy'],
            ['name' => 'Силянки', 'image_url' => 'https://res.cloudinary.com/dbmnxdhpp/image/upload/v1734437238/categories/gjluailqlryrehbbw4y8.avif', 'image_public_id' => 'categories/cctkxwbwjcqthmp6lvux'],
            ['name' => 'Пояси', 'image_url' => 'https://res.cloudinary.com/dbmnxdhpp/image/upload/v1734437367/categories/o9zblxlswmz5oyhcizq4.jpg', 'image_public_id' => 'categories/o9zblxlswmz5oyhcizq4'],
        ]);
    }
}
