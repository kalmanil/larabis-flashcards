<?php

namespace Tests\Unit\Flashcards;

use App\Features\Flashcards\Support\FormTypeCatalog;
use PHPUnit\Framework\TestCase;

final class FormTypeCatalogTest extends TestCase
{
    public function test_canonical_returns_allowed_label(): void
    {
        self::assertSame('noun (masc.)', FormTypeCatalog::canonical('noun (masc.)'));
    }

    public function test_canonical_resolves_alias(): void
    {
        self::assertSame('adjective', FormTypeCatalog::canonical('adj'));
    }

    public function test_canonical_case_insensitive_for_allowed(): void
    {
        self::assertSame('noun', FormTypeCatalog::canonical('NOUN'));
    }

    public function test_canonical_unknown_returns_null(): void
    {
        self::assertNull(FormTypeCatalog::canonical('not-a-real-label'));
    }

    public function test_normalize_drift_maps_en_dash_to_hyphen_for_known_label(): void
    {
        self::assertSame(
            'verb - hif\'il',
            FormTypeCatalog::canonical('verb '.pack('C*', 0xE2, 0x80, 0x93)." hif'il")
        );
    }

    public function test_from_form_type_id_returns_first_allowed(): void
    {
        self::assertSame(FormTypeCatalog::allowed()[0], FormTypeCatalog::fromFormTypeId(1));
    }

    public function test_resolve_from_import_entry_legacy_form_type_id_when_no_pos(): void
    {
        $first = FormTypeCatalog::allowed()[0];
        self::assertSame($first, FormTypeCatalog::resolveFromImportEntry([
            'form_type_id' => 1,
            'form_type' => 'wrong',
        ]));
    }

    public function test_word_import_system_instruction_contains_pos_id_not_form_type_id_list(): void
    {
        $s = FormTypeCatalog::wordImportSystemInstruction();
        self::assertStringContainsString('pos_id', $s);
        self::assertStringNotContainsString('form_type_id: integer from 1 to', $s);
        self::assertStringContainsString("hitpa'el", $s);
    }
}
