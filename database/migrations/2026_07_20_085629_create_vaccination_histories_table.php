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
        Schema::create('vaccination_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cow_id')->constrained()->onDelete('cascade');
            $table->string('vaccine_name');
            $table->date('administered_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_histories');
    }
};
