<?php

namespace Database\Seeders;

use App\Features\Flashcards\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Run from Larabis root for tenant DB:
     * php artisan tenants:seed --tenants=flashcards --class=Database\\Seeders\\LanguageSeeder
     *
     * Or if Larabis has a tenant seeder that calls this:
     * php artisan tenants:seed --tenants=flashcards
     */
    public function run(): void
    {
        $languages = [
            ['code' => 'he', 'name' => 'Hebrew'],
            ['code' => 'ru', 'name' => 'Russian'],
            ['code' => 'en', 'name' => 'English'],
        ];

        foreach ($languages as $lang) {
            Language::firstOrCreate(
                ['code' => $lang['code']],
                ['name' => $lang['name']]
            );
        }
    }
}
