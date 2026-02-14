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
        Schema::create('hebrew_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shoresh_id')->nullable()->constrained('shoresh')->nullOnDelete();
            $table->string('form_text');
            $table->string('form_type')->nullable();
            $table->string('transcription_ru')->nullable();
            $table->string('transcription_en')->nullable();
            $table->unsignedInteger('frequency_rank')->nullable();
            $table->decimal('frequency_per_million', 10, 2)->nullable();
            $table->timestamps();

            $table->index('shoresh_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hebrew_forms');
    }
};
