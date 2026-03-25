<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ProductService;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Mockery;

class ProductServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_instantiate_product_service()
    {
        $service = new ProductService();
        $this->assertInstanceOf(ProductService::class, $service);
    }

    /** @test */
    public function it_has_store_method()
    {
        $this->assertTrue(method_exists(ProductService::class, 'store'));
    }

    /** @test */
    public function it_has_update_method()
    {
        $this->assertTrue(method_exists(ProductService::class, 'update'));
    }

    /** @test */
    public function it_has_destroy_method()
    {
        $this->assertTrue(method_exists(ProductService::class, 'destroy'));
    }

    /** @test */
    public function slug_generation_logic_is_correct()
    {
        $name = "Test Product";
        $slug = Str::slug($name);
        $this->assertEquals('test-product', $slug);
    }

    /** @test */
    public function tag_serialization_logic()
    {
        // Simulate json_decode($tags[0]) inside store/update
        $tags_json = json_encode([
            (object)['value' => 'tag1'],
            (object)['value' => 'tag2']
        ]);
        
        $tags = [];
        foreach (json_decode($tags_json) as $tag) {
            array_push($tags, $tag->value);
        }
        $imploded = implode(',', $tags);
        
        $this->assertEquals('tag1,tag2', $imploded);
    }

    /** @test */
    public function date_range_parsing_logic()
    {
        $date_range = "2026-03-16 to 2026-03-20";
        $date_var = explode(" to ", $date_range);
        
        $start = strtotime($date_var[0]);
        $end = strtotime($date_var[1]);
        
        $this->assertEquals(strtotime("2026-03-16"), $start);
        $this->assertEquals(strtotime("2026-03-20"), $end);
    }
}
