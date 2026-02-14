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
        Schema::create('deck_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deck_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hebrew_form_id')->constrained('hebrew_forms')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['deck_id', 'hebrew_form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deck_cards');
    }
};
