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
        Schema::create('bead_producer_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bead_producer_id')->constrained()->onDelete('cascade');
            $table->enum('locale', ['uk', 'en']);
            $table->string('name', 100);
            $table->string('origin_country', 50);
            $table->unique(['bead_producer_id', 'locale']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bead_producer_translations');
    }
};
