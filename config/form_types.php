<?php

/**
 * Form type labels: canonical `allowed` values are derived from {@see \App\Features\Flashcards\Support\FormTypeComposer::allComposedLabels()}
 * merged with optional lists below (no need to duplicate the full verb/noun grid).
 *
 * @var array{
 *     extra_allowed?: list<string>,
 *     aliases: array<string, string>,
 * }
 */
return [
    /*
     * Optional strings accepted in forms/import in addition to composed labels
     * (legacy DB values, one-offs). Prefer extending FormTypeComposer for new patterns.
     */
    'extra_allowed' => [
        'verb - infinitive',
    ],

    'aliases' => [
        'adj' => 'adjective',
        'adjective (masc)' => 'adjective (masc.)',
        'adjective (fem)' => 'adjective (fem.)',
        'noun (masc)' => 'noun (masc.)',
        'noun (fem)' => 'noun (fem.)',
        'noun pl' => 'noun (pl.)',
        'noun pl.' => 'noun (pl.)',
        'adv' => 'adverb',
        'interj' => 'interjection',
        'prep' => 'preposition',
        'conj' => 'conjunction',
        'pron' => 'pronoun',
        'prop' => 'proper noun',
        'proper_noun' => 'proper noun',
        'num' => 'numeral',
        'verb inf' => 'verb - infinitive',
        'infinitive' => 'verb - infinitive',
        'qal' => 'verb - qal',
        'nifal' => 'verb - nif\'al',
        'nif\'al' => 'verb - nif\'al',
        'piel' => 'verb - pi\'el',
        'pi\'el' => 'verb - pi\'el',
        'pual' => 'verb - pu\'al',
        'hifil' => 'verb - hif\'il',
        'hif\'il' => 'verb - hif\'il',
        'hufal' => 'verb - huf\'al',
        'hitpael' => 'verb - hitpa\'el',
        'hitpa\'el' => 'verb - hitpa\'el',
    ],
];
