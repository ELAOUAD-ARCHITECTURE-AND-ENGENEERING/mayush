<?php

namespace Tests\Unit\Utilities;

use Tests\TestCase;
use App\Utility\ProductUtility;

/**
 * ProductUtilityTest
 *
 * Tests get_attribute_options and get_combination_string utilities.
 * The combination string builder is pure logic and fully testable without DB.
 */
class ProductUtilityTest extends TestCase
{
    // ─── get_combination_string ───────────────────────────────────────────────

    /** @test */
    public function combination_string_builds_single_item_without_dash(): void
    {
        $combination = ['Large'];
        $collection  = collect(['colors_active' => false]);

        $result = ProductUtility::get_combination_string($combination, $collection);

        $this->assertEquals('Large', $result);
    }

    /** @test */
    public function combination_string_joins_multiple_items_with_dash(): void
    {
        $combination = ['Red', 'Large', 'Cotton'];
        $collection  = collect(['colors_active' => false]);

        $result = ProductUtility::get_combination_string($combination, $collection);

        $this->assertEquals('Red-Large-Cotton', $result);
    }

    /** @test */
    public function combination_string_removes_spaces(): void
    {
        $combination = ['Light Blue', 'Extra Large'];
        $collection  = collect(['colors_active' => false]);

        $result = ProductUtility::get_combination_string($combination, $collection);

        $this->assertEquals('LightBlue-ExtraLarge', $result);
    }

    /** @test */
    public function combination_string_empty_gives_empty_string(): void
    {
        $combination = [];
        $collection  = collect(['colors_active' => false]);

        $result = ProductUtility::get_combination_string($combination, $collection);

        $this->assertEquals('', $result);
    }

    // ─── get_attribute_options ────────────────────────────────────────────────

    /** @test */
    public function get_attribute_options_returns_empty_array_when_no_colors_and_no_choices(): void
    {
        $collection = collect([
            'colors_active' => false,
            'choice_no'     => [],
        ]);

        $result = ProductUtility::get_attribute_options($collection);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function get_attribute_options_includes_colors_when_active(): void
    {
        $collection = collect([
            'colors_active' => true,
            'colors'        => ['#ff0000', '#00ff00'],
            'choice_no'     => [],
        ]);

        $result = ProductUtility::get_attribute_options($collection);

        $this->assertNotEmpty($result);
        $this->assertContains(['#ff0000', '#00ff00'], $result);
    }

    /** @test */
    public function class_exists(): void
    {
        $this->assertTrue(class_exists(ProductUtility::class));
    }

    /** @test */
    public function get_combination_string_is_static(): void
    {
        $ref = new \ReflectionMethod(ProductUtility::class, 'get_combination_string');
        $this->assertTrue($ref->isStatic());
    }

    /** @test */
    public function get_attribute_options_is_static(): void
    {
        $ref = new \ReflectionMethod(ProductUtility::class, 'get_attribute_options');
        $this->assertTrue($ref->isStatic());
    }
}
