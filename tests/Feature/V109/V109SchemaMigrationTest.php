<?php

namespace Tests\Feature\V109;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tests\Traits\SeedsAppConfigs;

class V109SchemaMigrationTest extends TestCase
{
    use RefreshDatabase, SeedsAppConfigs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedConfigs();
    }

    public function test_v109_schema_and_settings_are_available(): void
    {
        $this->assertTrue(Schema::hasColumn('products', 'promotional'));
        $this->assertTrue(Schema::hasColumn('orders', 'invoice_number'));
        $this->assertTrue(Schema::hasColumn('orders', 'order_note'));
        $this->assertTrue(Schema::hasColumn('wallets', 'added_by'));
        $this->assertTrue(Schema::hasColumn('permissions', 'section'));
        $this->assertTrue(Schema::hasTable('payment_informations'));
        $this->assertTrue(Schema::hasTable('ai_prompts'));
        $this->assertTrue(Schema::hasTable('ai_usage_logs'));

        foreach (['invoice_config', 'shipping_label', 'thermal_printer', 'ai_activation', 'gemini_model'] as $type) {
            $this->assertDatabaseHas('business_settings', ['type' => $type]);
        }

        $this->assertDatabaseHas('permissions', [
            'name' => 'view_promotion_and_offers_dashboard',
            'section' => 'promotion_and_offers',
        ]);
    }
}
