<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('delivery_types')->insertOrIgnore([
            ['type' => 'pickup', 'name' => 'Самовивіз з наших магазинів'],
            ['type' => 'pickup', 'name' => 'Самовивіз з поштоматів Нової Пошти'],
            ['type' => 'pickup', 'name' => 'Самовивіз з Нової Пошти'],
            ['type' => 'pickup', 'name' => 'Самовивіз з УКРПОШТИ'],
            ['type' => 'courier', 'name' => 'Кур\'єр Нової Пошти'],
            ['type' => 'courier', 'name' => 'Кур\'єр УКРПОШТИ'],

        ]);
    }
}
