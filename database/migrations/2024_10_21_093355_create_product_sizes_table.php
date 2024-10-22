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
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->unsignedBigInteger('product_description_id');  
            $table->unsignedBigInteger('size_id');   

            $table->primary(['product_description_id', 'size_id']);  

            $table->foreign('product_description_id')
                  ->references('id')
                  ->on('product_descriptions')
                  ->onDelete('cascade');

            $table->foreign('size_id')
                  ->references('id')
                  ->on('sizes')
                  ->onDelete('cascade');

            $table->timestamps();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
    }
};
