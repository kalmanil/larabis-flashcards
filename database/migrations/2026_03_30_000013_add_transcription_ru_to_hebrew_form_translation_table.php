<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Same migration as Larabis `database/migrations/tenant/2026_03_30_000013_*`.
 * Use guarded changes so running both paths does not fail.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hebrew_form_translation')) {
            return;
        }
        if (Schema::hasColumn('hebrew_form_translation', 'transcription_ru')) {
            return;
        }
        Schema::table('hebrew_form_translation', function (Blueprint $table) {
            $table->string('transcription_ru')->nullable()->after('sense_order');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('hebrew_form_translation')) {
            return;
        }
        if (! Schema::hasColumn('hebrew_form_translation', 'transcription_ru')) {
            return;
        }
        Schema::table('hebrew_form_translation', function (Blueprint $table) {
            $table->dropColumn('transcription_ru');
        });
    }
};
