<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: Runs on tenant database via tenants:migrate from Larabis root.
     */
    public function up(): void
    {
        Schema::create('hebrew_form_translation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hebrew_form_id')->constrained('hebrew_forms')->cascadeOnDelete();
            $table->foreignId('translation_id')->constrained('translations')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['hebrew_form_id', 'translation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hebrew_form_translation');
    }
};
