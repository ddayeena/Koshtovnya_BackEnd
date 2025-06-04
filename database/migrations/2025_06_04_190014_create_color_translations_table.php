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
        Schema::create('color_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('color_id')->constrained()->onDelete('cascade');
            $table->enum('locale', ['uk', 'en']); 
            $table->string('color_name', 50); 
            $table->unique(['color_id', 'locale']); 
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('color_translations');
    }
};
