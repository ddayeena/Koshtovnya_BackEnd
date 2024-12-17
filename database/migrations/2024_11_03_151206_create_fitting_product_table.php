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
        Schema::create('fitting_product', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('product_id'); 
            $table->unsignedBigInteger('fitting_id'); 
            $table->unsignedBigInteger('material_id'); 
            $table->integer('quantity')->unsigned(); 

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
            $table->foreign('fitting_id')
                   ->references('id')
                   ->on('fittings')
                   ->onDelete('cascade');

                   $table->foreign('material_id')
                   ->references('id')
                   ->on('materials')
                   ->onDelete('cascade');

            $table->timestamps(); 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_fittings');
    }
};
