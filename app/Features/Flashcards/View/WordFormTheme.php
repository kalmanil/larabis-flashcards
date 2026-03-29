<?php

declare(strict_types=1);

namespace App\Features\Flashcards\View;

final class WordFormTheme
{
    /**
     * @return array<string, string>
     */
    public static function variables(string $theme = 'default'): array
    {
        $theme = $theme !== '' ? $theme : 'default';

        $inputClass = $theme === 'default'
            ? 'w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'
            : 'w-full border rounded px-3 py-2';
        $inputClassLg = $theme === 'default'
            ? 'w-full border border-gray-200 rounded-xl px-3 py-2 text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'
            : 'w-full border rounded px-3 py-2 text-lg';
        $btnPrimary = $theme === 'default'
            ? 'px-3 py-2 text-sm bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none'
            : 'px-3 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700';
        $btnStress = $theme === 'default'
            ? 'px-3 py-2 text-sm bg-indigo-100 text-indigo-800 rounded-xl hover:bg-indigo-200 focus:ring-2 focus:ring-indigo-500'
            : 'px-3 py-2 text-sm bg-gray-200 text-gray-800 rounded hover:bg-gray-300';
        $wordFormSpacing = $theme === 'default' ? 'space-y-5' : 'space-y-4';
        $wordFormBtnSubmit = $theme === 'default'
            ? 'px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 shadow-md transition-all duration-200'
            : 'px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700';
        $wordFormBtnSecondary = $theme === 'default'
            ? 'px-6 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-medium transition-colors'
            : 'px-4 py-2 border rounded hover:bg-gray-50';
        $wordFormInputBorderJs = $theme === 'default'
            ? 'border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500'
            : 'border rounded px-3 py-2';

        return compact(
            'theme',
            'inputClass',
            'inputClassLg',
            'btnPrimary',
            'btnStress',
            'wordFormSpacing',
            'wordFormBtnSubmit',
            'wordFormBtnSecondary',
            'wordFormInputBorderJs'
        );
    }
}
