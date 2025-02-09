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
            $table->string('last_name',50);
            $table->string('first_name',50);
            $table->string('second_name',50);
            $table->string('phone_number',20)->unique();
            $table->enum('status',['В очікуванні', 'Відправлено', 'Доставлено'])->default('В очікуванні'); 
            $table->decimal('total_amount', 8, 2);
            
            $table->foreign('user_id')
            ->references('id')
            ->on('users')
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
