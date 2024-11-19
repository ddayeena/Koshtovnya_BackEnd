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
        Schema::create('fittings', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->enum('type_of_fitting',
                ['Метал','Пластик','Дерево','Скло','Кераміка','Натуральна шкіра','Еко-шкіра'])
                ->default('Метал');
            $table->float('cost_per_unit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fittings');
    }
};
