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
        Schema::create('product_descriptions', function (Blueprint $table) {
            $table->id();  
            $table->string('material', 255);  // Матеріал товару
            $table->decimal('weight', 10, 2);  // Вага товару
            $table->string('bead_manufacturer', 255);  // Виробник бісеру
            $table->string('country_of_manufacture', 100);  // Країна виробник
            $table->unsignedBigInteger('category_id');  // Зовнішній ключ на таблицю категорій

            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');

            $table->timestamps();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_descriptions');
    }
};
