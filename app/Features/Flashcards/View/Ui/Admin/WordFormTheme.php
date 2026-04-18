<?php

declare(strict_types=1);

namespace App\Features\Flashcards\View\Ui\Admin;

use App\Features\Flashcards\View\Contracts\WordFormThemeInterface;

class WordFormTheme implements WordFormThemeInterface
{
    public function variables(): array
    {
        $theme = 'admin';

        return [
            'theme' => $theme,
            'inputClass' => 'w-full border rounded px-3 py-2',
            'inputClassLg' => 'w-full border rounded px-3 py-2 text-lg',
            'btnPrimary' => 'px-3 py-2 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700',
            'btnStress' => 'px-3 py-2 text-sm bg-gray-200 text-gray-800 rounded hover:bg-gray-300',
            'btnStressSmall' => 'px-2 py-1 text-xs bg-gray-200 text-gray-800 rounded hover:bg-gray-300 shrink-0',
            'wordFormSpacing' => 'space-y-4',
            'wordFormBtnSubmit' => 'px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700',
            'wordFormBtnSecondary' => 'px-4 py-2 border rounded hover:bg-gray-50',
            'wordFormInputBorderJs' => 'border rounded px-3 py-2',
        ];
    }
}
