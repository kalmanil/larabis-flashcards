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
        Schema::table('hebrew_forms', function (Blueprint $table) {
            $table->boolean('enrichment_pending')->default(false)->after('frequency_per_million');
            $table->index('enrichment_pending');
        });
    }

    public function down(): void
    {
        Schema::table('hebrew_forms', function (Blueprint $table) {
            $table->dropIndex(['enrichment_pending']);
            $table->dropColumn('enrichment_pending');
        });
    }
};
