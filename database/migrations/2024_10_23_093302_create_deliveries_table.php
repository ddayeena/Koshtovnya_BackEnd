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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');


            $table->enum('post_service', ['Укрпошта', 'Нова Пошта'])->default('Нова Пошта');
            $table->string('city');
            $table->string('post_office')->nullable();
            $table->enum('delivery_type', ['warehouse', 'courier'])->default('warehouse'); 
            $table->string('delivery_address')->nullable();
            $table->decimal('cost', 8, 2);


            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
