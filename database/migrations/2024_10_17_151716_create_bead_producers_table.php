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
        Schema::create('bead_producers', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('origin_country',50);
            $table->float('cost_per_gram');   
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bead_producers');
    }
};
