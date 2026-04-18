<?php

declare(strict_types=1);

namespace App\Features\Flashcards\View\Ui\Default;

use App\Features\Flashcards\View\Contracts\WordFormThemeInterface;

class WordFormTheme implements WordFormThemeInterface
{
    public function variables(): array
    {
        $theme = 'default';

        return [
            'theme' => $theme,
            'inputClass' => 'w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
            'inputClassLg' => 'w-full border border-gray-200 rounded-xl px-3 py-2 text-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
            'btnPrimary' => 'px-4 py-2 text-sm bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none',
            'btnStress' => 'px-3 py-2 text-sm bg-indigo-100 text-indigo-800 rounded-xl hover:bg-indigo-200 focus:ring-2 focus:ring-indigo-500',
            'btnStressSmall' => 'px-2 py-1 text-xs bg-indigo-100 text-indigo-800 rounded-lg hover:bg-indigo-200 shrink-0',
            'wordFormSpacing' => 'space-y-5',
            'wordFormBtnSubmit' => 'px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 shadow-md transition-all duration-200',
            'wordFormBtnSecondary' => 'px-6 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 font-medium transition-colors',
            'wordFormInputBorderJs' => 'border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
        ];
    }
}
