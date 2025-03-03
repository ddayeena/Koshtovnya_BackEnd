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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->enum('payment_method', ['Післяоплата', 'Оплата картою', 'Передоплата'])->default('Післяоплата');
            $table->string('transaction_number')->unique()->nullable();
            $table->decimal('amount', 8, 2);
            $table->enum('status', ['В очікуванні', 'Оплачено', 'Помилка'])->default('В очікуванні');
            $table->timestamp('paid_at')->nullable()->default(null);
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
        Schema::dropIfExists('payments');
    }
};
