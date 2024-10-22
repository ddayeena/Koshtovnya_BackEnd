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
        Schema::create('product_colors', function (Blueprint $table) {
            $table->unsignedBigInteger('product_description_id'); 
            $table->unsignedBigInteger('color_id');   

            $table->primary(['product_description_id', 'color_id']); 

            $table->foreign('product_description_id')
                  ->references('id')
                  ->on('product_descriptions')
                  ->onDelete('cascade');

            $table->foreign('color_id')
                  ->references('id')
                  ->on('colors')
                  ->onDelete('cascade');
            $table->timestamps();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};
