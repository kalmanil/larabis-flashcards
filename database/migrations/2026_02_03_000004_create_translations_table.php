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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('text');
            $table->timestamps();

            $table->index('language_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
