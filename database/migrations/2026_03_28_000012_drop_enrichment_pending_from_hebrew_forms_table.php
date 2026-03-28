<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Pending state is derived: a word needs processing when it has no Russian translation
     * (see HebrewForm::scopePendingEnrichment).
     */
    public function up(): void
    {
        Schema::table('hebrew_forms', function (Blueprint $table) {
            if (Schema::hasColumn('hebrew_forms', 'enrichment_pending')) {
                $table->dropIndex(['enrichment_pending']);
                $table->dropColumn('enrichment_pending');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hebrew_forms', function (Blueprint $table) {
            $table->boolean('enrichment_pending')->default(false)->after('frequency_per_million');
            $table->index('enrichment_pending');
        });
    }
};
