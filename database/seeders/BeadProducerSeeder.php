<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class BeadProducerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('bead_producers')->insertOrIgnore([
            ['name' => 'Японський бісер', 'origin_country' => 'Японія', 'cost_per_gram' => 15],
            ['name' => 'Китайський бісер', 'origin_country' => 'Китай', 'cost_per_gram' => 0.5],
            ['name' => 'Чешський бісер', 'origin_country' => 'Чехія', 'cost_per_gram' => 4],
            ['name' => 'Український бісер', 'origin_country' => 'Україна', 'cost_per_gram' => 1],
        ]);
    }
}