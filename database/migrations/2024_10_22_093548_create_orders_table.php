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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->dateTime('order_date');
            $table->unsignedBigInteger('payment_id'); 
            $table->unsignedBigInteger('delivery_id'); 
            $table->enum('status',['В очікуванні', 'Відправлено', 'Доставлено']); 
            $table->decimal('total_amount', 8, 2);
            
            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');

            $table->foreign('payment_id')
                  ->references('id')
                  ->on('payments')
                  ->onDelete('cascade');

            $table->foreign('delivery_id')
                  ->references('id')
                  ->on('deliveries')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
