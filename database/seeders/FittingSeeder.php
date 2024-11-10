<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FittingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('fittings')->insertOrIgnore([
            ['name' => 'Застібка',  'cost_per_unit' => 30],
            ['name' => 'Кільця', 'cost_per_unit' => 10],
            ['name' => 'Роздільники', 'cost_per_unit' => 25],
        ]);
    }
}
