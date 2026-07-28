<?php

namespace Tests\Unit\Services;

use App\Services\SearchQueryNormalizer;
use Tests\TestCase;

class SearchQueryNormalizerTest extends TestCase
{
    public function test_normalization_preserves_original_and_keeps_numeric_tokens(): void
    {
        $result = app(SearchQueryNormalizer::class)->normalize('  Table   160x200 5zana  ');

        $this->assertSame('Table   160x200 5zana', $result['original']);
        $this->assertSame('table 160x200 5zana', $result['normalized']);
        $this->assertContains('160x200', $result['tokens']);
        $this->assertContains('5zana', $result['tokens']);
        $this->assertTrue($result['language_signals']['has_latin']);
        $this->assertTrue($result['language_signals']['has_digits']);
    }

    public function test_normalization_detects_arabic_and_mixed_script_queries(): void
    {
        $result = app(SearchQueryNormalizer::class)->normalize('طاولة beige');

        $this->assertTrue($result['language_signals']['has_arabic']);
        $this->assertTrue($result['language_signals']['has_latin']);
        $this->assertTrue($result['language_signals']['mixed_script']);
    }

    public function test_normalization_is_bounded_without_losing_original_input(): void
    {
        config(['search.query.max_length' => 8]);

        $result = app(SearchQueryNormalizer::class)->normalize('abcdefghijk');

        $this->assertSame('abcdefghijk', $result['original']);
        $this->assertSame('abcdefgh', $result['normalized']);
        $this->assertTrue($result['is_truncated']);
        $this->assertFalse(app(SearchQueryNormalizer::class)->isWithinBounds('abcdefghijk'));
    }

    public function test_hash_is_deterministic_and_does_not_expose_query_text(): void
    {
        $result = app(SearchQueryNormalizer::class)->normalize('Chaise confortable');

        $this->assertSame($result['hash'], app(SearchQueryNormalizer::class)->normalize('chaise confortable')['hash']);
        $this->assertNotSame('chaise confortable', $result['hash']);
    }
}
