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
            $table->unsignedBigInteger('bead_producer_id');  
            $table->decimal('weight', 10, 2); 
            $table->string('country_of_manufacture', 100);  // Країна виробник
            $table->enum('type_of_bead', ['Матовий', 'Не матовий'])->default('Не матовий');
            $table->unsignedBigInteger('category_id');  

            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');

            $table->foreign('bead_producer_id')
                  ->references('id')
                  ->on('bead_producers')
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
