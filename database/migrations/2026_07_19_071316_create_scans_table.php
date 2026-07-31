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
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cow_id')->constrained()->onDelete('cascade');
            $table->json('questionnaire_data');
            $table->text('description');
            $table->string('fmd_risk');
            $table->string('lsd_risk');
            $table->decimal('confidence_score', 5, 2);
            $table->text('explanation');
            $table->text('recommendation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};
