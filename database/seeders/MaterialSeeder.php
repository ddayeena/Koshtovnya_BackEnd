<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('materials')->insertOrIgnore([
            ['name' => 'Метал'],
            ['name' => 'Пластик'],
            ['name' => 'Скло'],
            ['name' => 'Кераміка'],
            ['name' => 'Шкіра'],
        ]);
    }
}
