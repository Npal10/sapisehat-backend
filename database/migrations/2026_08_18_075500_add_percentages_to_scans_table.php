<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->decimal('pmk_percentage', 5, 2)->default(0.0)->after('lsd_risk');
            $table->decimal('lsd_percentage', 5, 2)->default(0.0)->after('pmk_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn(['pmk_percentage', 'lsd_percentage']);
        });
    }
};
