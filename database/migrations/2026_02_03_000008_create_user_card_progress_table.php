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
        Schema::create('user_card_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deck_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hebrew_form_id')->constrained('hebrew_forms')->cascadeOnDelete();
            $table->boolean('known')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'deck_id', 'hebrew_form_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_card_progress');
    }
};
