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
        Schema::table('cows', function (Blueprint $table) {
            $table->string('photo_url')->nullable()->after('weight');
            $table->enum('status', ['Tersedia', 'Terjual'])->default('Tersedia')->after('photo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cows', function (Blueprint $table) {
            $table->dropColumn(['photo_url', 'status']);
        });
    }
};
