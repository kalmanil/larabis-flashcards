<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Seeds languages (he, ru, en) for the flashcards tenant.
     */
    public function up(): void
    {
        $languages = [
            ['code' => 'he', 'name' => 'Hebrew'],
            ['code' => 'ru', 'name' => 'Russian'],
            ['code' => 'en', 'name' => 'English'],
        ];

        foreach ($languages as $lang) {
            DB::table('languages')->insertOrIgnore([
                'code' => $lang['code'],
                'name' => $lang['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('languages')->whereIn('code', ['he', 'ru', 'en'])->delete();
    }
};
