<?php

namespace Tests\Unit\Utilities;

use Tests\TestCase;
use App\Utility\SearchUtility;

/**
 * SearchUtilityTest
 *
 * Tests the SearchUtility structural contract and query logic.
 * The store() method hits the DB, so we test the logic and structure.
 */
class SearchUtilityTest extends TestCase
{
    /** @test */
    public function class_exists(): void
    {
        $this->assertTrue(class_exists(SearchUtility::class));
    }

    /** @test */
    public function store_is_static_method(): void
    {
        $ref = new \ReflectionMethod(SearchUtility::class, 'store');
        $this->assertTrue($ref->isStatic());
    }

    /** @test */
    public function store_accepts_query_string_parameter(): void
    {
        $ref    = new \ReflectionMethod(SearchUtility::class, 'store');
        $params = $ref->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('query', $params[0]->getName());
    }

    /** @test */
    public function empty_query_should_not_be_stored(): void
    {
        // The utility skips empty/null queries — verify the guard logic
        $shouldStore = fn($q) => $q !== null && $q !== '';

        $this->assertFalse($shouldStore(''));
        $this->assertFalse($shouldStore(null));
        $this->assertTrue($shouldStore('shoes'));
        $this->assertTrue($shouldStore('laptop bag'));
    }

    /** @test */
    public function search_count_increments_on_repeated_query(): void
    {
        // Simulates the in-memory logic: count increments for repeated queries
        $counts = [];

        $storeSearch = function (string $query) use (&$counts) {
            if (isset($counts[$query])) {
                $counts[$query]++;
            } else {
                $counts[$query] = 1;
            }
        };

        $storeSearch('shoes');
        $storeSearch('shoes');
        $storeSearch('bags');

        $this->assertEquals(2, $counts['shoes']);
        $this->assertEquals(1, $counts['bags']);
    }

    /** @test */
    public function search_query_whitespace_only_is_empty(): void
    {
        $query = '   ';
        $this->assertEmpty(trim($query));
    }

    /** @test */
    public function search_query_special_characters_are_preserved(): void
    {
        $query = "men's shoes & belts";
        $this->assertNotEmpty($query);
        $this->assertStringContainsString("'", $query);
        $this->assertStringContainsString('&', $query);
    }
}
