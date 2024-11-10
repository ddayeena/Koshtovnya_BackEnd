<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fittings', function (Blueprint $table) {
            $table->enum('type_of_fitting',
            ['Метал','Пластик','Дерево','Скло','Кераміка','Натуральна шкіра','Еко-шкіра'])
            ->default('Метал')
            ->after('name');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fittings', function (Blueprint $table) {
            $table->dropColumn('type_of_fitting');
        });
    }
};
