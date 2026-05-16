<?php

namespace Tests\Unit\Flashcards;

use App\Features\Flashcards\Support\FormTypeComposer;
use App\Features\Flashcards\Support\FormTypeCatalog;
use PHPUnit\Framework\TestCase;

final class FormTypeComposerTest extends TestCase
{
    public function test_compose_noun_masc(): void
    {
        self::assertSame('noun (masc.)', FormTypeComposer::compose([
            'pos_id' => 1,
            'noun_gender_id' => 1,
            'noun_number_id' => 1,
        ]));
    }

    public function test_compose_verb_hifil_infinitive(): void
    {
        self::assertSame('verb - hif\'il infinitive', FormTypeComposer::compose([
            'pos_id' => 2,
            'verb_binyan_id' => 5,
            'verb_shape_id' => 2,
        ]));
    }

    public function test_compose_verb_hufal_past_3ms(): void
    {
        self::assertSame('verb - huf\'al past (3ms)', FormTypeComposer::compose([
            'pos_id' => 2,
            'verb_binyan_id' => 6,
            'verb_shape_id' => 3,
        ]));
    }

    public function test_compose_proper_noun(): void
    {
        self::assertSame('proper noun', FormTypeComposer::compose(['pos_id' => 10]));
    }

    public function test_compose_adjective_fem_plural(): void
    {
        self::assertSame('adjective (fem. pl.)', FormTypeComposer::compose([
            'pos_id' => 3,
            'adj_gender_id' => 2,
            'adj_number_id' => 4,
        ]));
    }

    public function test_resolve_prefers_factors_over_legacy_id(): void
    {
        self::assertSame('noun', FormTypeCatalog::resolveFromImportEntry([
            'pos_id' => 1,
            'form_type_id' => 2,
        ]));
    }
}
