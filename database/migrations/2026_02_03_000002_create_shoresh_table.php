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
        Schema::create('shoresh', function (Blueprint $table) {
            $table->id();
            $table->string('root');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoresh');
    }
};
