<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah kolom photo_base64 untuk menyimpan foto langsung di DB (agar tidak hilang saat Railway re-deploy)
            $table->longText('photo_base64')->nullable()->after('photo_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo_base64');
        });
    }
};
