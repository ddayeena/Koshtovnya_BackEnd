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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('warnings_count')->default(0)->after('access');
            $table->boolean('is_permanently_banned')->default(false)->after('warnings_count');
            $table->timestamp('banned_until')->nullable()->after('is_permanently_banned');
            $table->boolean('was_banned_before')->default(false)->after('banned_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['warnings_count', 'is_permanently_banned', 'banned_until', 'was_banned_before']);
        });
    }
};
