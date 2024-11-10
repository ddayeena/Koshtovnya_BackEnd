<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('expenses')->insertOrIgnore([
            ['type_of_expense' => 'Упаковка', 'description' => 'Коробки та пакети', 'cost' => 1500],
            ['type_of_expense' => 'Інструменти', 'description' => 'Нитки та голки', 'cost' => 500],
        ]);
    }
}
