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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('last_name',50)->nullable();
            $table->string('first_name',50);
            $table->string('second_name',50)->nullable();
            $table->enum('role', ['superadmin', 'admin', 'user','manager'])->default('user');
            $table->string('email')->unique();
            $table->string('phone_number',20)->unique()->nullable();
            $table->string('password');
            $table->boolean('access')->default(1);
            $table->timestamps();
        });
        // $table->rememberToken();

        // Schema::create('sessions', function (Blueprint $table) {
        //     $table->string('id')->primary();
        //     $table->foreignId('user_id')->nullable()->index();
        //     $table->string('ip_address', 45)->nullable();
        //     $table->text('user_agent')->nullable();
        //     $table->longText('payload');
        //     $table->integer('last_activity')->index();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        // Schema::dropIfExists('sessions');
    }
};
