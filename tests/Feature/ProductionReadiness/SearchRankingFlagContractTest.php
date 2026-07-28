<?php

namespace Tests\Feature\ProductionReadiness;

use Tests\TestCase;

class SearchRankingFlagContractTest extends TestCase
{
    /** @test */
    public function improved_mysql_ranking_is_off_by_default(): void
    {
        $this->assertFalse(config('search.features.improved_mysql'));

        $config = file_get_contents(base_path('config/search.php'));

        $this->assertNotFalse($config);
        $this->assertStringContainsString(
            "'improved_mysql' => (bool) env('MYSQL_IMPROVED_SEARCH', false)",
            $config
        );
    }

    /** @test */
    public function the_flag_can_represent_both_disabled_and_enabled_contract_states(): void
    {
        config(['search.features.improved_mysql' => false]);
        $this->assertFalse(config('search.features.improved_mysql'));

        config(['search.features.improved_mysql' => true]);
        $this->assertTrue(config('search.features.improved_mysql'));
    }

    /** @test */
    public function storefront_relevance_ordering_is_guarded_by_the_flag_in_both_search_branches(): void
    {
        $source = file_get_contents(base_path('app/Http/Controllers/SearchController.php'));

        $this->assertNotFalse($source);
        $this->assertSame(
            2,
            preg_match_all(
                <<<'REGEX'
/if \(empty\(\$sort_by\) && config\('search\.features\.improved_mysql', false\)\) \{\s*\$products->orderByRaw\(\s*'\(MATCH\(name, tags\) AGAINST \(\? IN BOOLEAN MODE\) \* 10\)/s
REGEX,
                $source
            )
        );
    }

    /** @test */
    public function api_relevance_ordering_is_guarded_by_the_flag(): void
    {
        $source = file_get_contents(base_path('app/Http/Controllers/Api/V2/ProductController.php'));

        $this->assertNotFalse($source);
        $this->assertSame(
            1,
            preg_match_all(
                <<<'REGEX'
/if \(config\('search\.features\.improved_mysql', false\)\) \{\s*\$products->orderByRaw\(\s*'CASE\s+WHEN name LIKE \? THEN 1\s+WHEN name LIKE \? THEN 2\s+ELSE 3/s
REGEX,
                $source
            )
        );
    }

    /** @test */
    public function autocomplete_relevance_ordering_is_guarded_by_the_flag(): void
    {
        $source = file_get_contents(base_path('app/Http/Controllers/SearchController.php'));

        $this->assertNotFalse($source);
        $this->assertStringContainsString(
            "->when(config('search.features.improved_mysql', false), function (\$query) use (\$booleanQuery)",
            $source
        );
    }
}
