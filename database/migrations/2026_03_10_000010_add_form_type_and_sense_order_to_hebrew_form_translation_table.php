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
        Schema::table('hebrew_form_translation', function (Blueprint $table) {
            $table->string('form_type')->nullable()->after('translation_id');
            $table->unsignedSmallInteger('sense_order')->nullable()->after('form_type');
        });
    }

    public function down(): void
    {
        Schema::table('hebrew_form_translation', function (Blueprint $table) {
            $table->dropColumn(['form_type', 'sense_order']);
        });
    }
};

