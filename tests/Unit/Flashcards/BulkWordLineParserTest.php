<?php

namespace Tests\Unit\Flashcards;

use App\Features\Flashcards\Services\BulkWordLineParser;
use PHPUnit\Framework\TestCase;

class BulkWordLineParserTest extends TestCase
{
    public function test_trims_splits_and_dedupes_preserving_order(): void
    {
        $out = BulkWordLineParser::uniqueLines(" א \r\nב\nא\n\nב ");

        $this->assertSame(['א', 'ב'], $out);
    }

    public function test_handles_crlf(): void
    {
        $out = BulkWordLineParser::uniqueLines("שלום\r\nבית");

        $this->assertSame(['שלום', 'בית'], $out);
    }
}
