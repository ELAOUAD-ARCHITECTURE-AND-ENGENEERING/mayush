<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Services\FlashDealStockService;
use Illuminate\Support\Collection;

class FlashDealStockServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the translations table for the translate() helper in sqlite in-memory
        if (!\Illuminate\Support\Facades\Schema::hasTable('translations')) {
            \Illuminate\Support\Facades\Schema::create('translations', function ($table) {
                $table->string('lang_key')->nullable();
                $table->string('lang_value')->nullable();
                $table->string('lang')->nullable();
                $table->timestamps();
            });
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('app_translations')) {
            \Illuminate\Support\Facades\Schema::create('app_translations', function ($table) {
                $table->string('lang_key')->nullable();
                $table->string('lang_value')->nullable();
                $table->string('lang')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Helper to mock a product with specific stock and sales.
     */
    private function createMockProduct($qty, $sold)
    {
        $product = new Product();
        $product->num_of_sale = $sold;

        // Mock the 'stocks' relationship as a collection so $product->stocks->sum('qty') works
        $stockItem = new \stdClass();
        $stockItem->qty = $qty;
        $product->setRelation('stocks', new Collection([$stockItem]));

        return $product;
    }

    public function test_it_returns_green_for_80_percent_or_more_stock()
    {
        // 80 qty remaining, 20 sold -> Total 100 -> 80% remaining
        $product = $this->createMockProduct(80, 20);
        $data = FlashDealStockService::getStockData($product);

        $this->assertEquals(80, $data['percentage']);
        $this->assertEquals('#28a745', $data['color']); // Green
        $this->assertEquals('80 ' . translate('left in stock'), $data['text']);
    }

    public function test_it_returns_orange_for_50_to_79_percent_stock()
    {
        // 50 qty remaining, 50 sold -> Total 100 -> 50% remaining
        $product = $this->createMockProduct(50, 50);
        $data = FlashDealStockService::getStockData($product);

        $this->assertEquals(50, $data['percentage']);
        $this->assertEquals('#fd7e14', $data['color']); // Orange
        $this->assertEquals('50 ' . translate('left in stock'), $data['text']);

        // 79 qty remaining, 21 sold -> 79% remaining
        $product2 = $this->createMockProduct(79, 21);
        $data2 = FlashDealStockService::getStockData($product2);
        $this->assertEquals('#fd7e14', $data2['color']); // Orange
    }

    public function test_it_returns_red_for_less_than_50_percent_stock()
    {
        // 49 qty remaining, 51 sold -> Total 100 -> 49% remaining
        $product = $this->createMockProduct(49, 51);
        $data = FlashDealStockService::getStockData($product);

        $this->assertEquals(49, $data['percentage']);
        $this->assertEquals('#dc3545', $data['color']); // Red
        $this->assertEquals('49 ' . translate('left in stock'), $data['text']);
    }

    public function test_it_returns_out_of_stock_text_when_qty_is_zero()
    {
        // 0 qty remaining, 100 sold -> Total 100 -> 0% remaining
        $product = $this->createMockProduct(0, 100);
        $data = FlashDealStockService::getStockData($product);

        $this->assertEquals(0, $data['percentage']);
        $this->assertEquals('#dc3545', $data['color']); // Red
        $this->assertEquals(translate('Out of stock'), $data['text']);
    }

    public function test_it_handles_zero_total_qty_gracefully()
    {
        // 0 qty, 0 sold -> Total 0 -> Fallback to 100%
        $product = $this->createMockProduct(0, 0);
        $data = FlashDealStockService::getStockData($product);

        $this->assertEquals(100, $data['percentage']);
        $this->assertEquals('#28a745', $data['color']); // Green (100 >= 80)
        $this->assertEquals(translate('Out of stock'), $data['text']);
    }
}
