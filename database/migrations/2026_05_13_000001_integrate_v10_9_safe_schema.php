<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addProductColumns();
        $this->addOrderColumns();
        $this->addWalletColumns();
        $this->addPermissionColumns();
        $this->createPaymentInformationTable();
        $this->createAiTables();
        $this->seedSettingsAndPermissions();
        $this->markExistingPromotionalProducts();
    }

    public function down(): void
    {
        if (Schema::hasTable('ai_usage_logs')) {
            Schema::dropIfExists('ai_usage_logs');
        }

        if (Schema::hasTable('ai_prompts')) {
            Schema::dropIfExists('ai_prompts');
        }

        if (Schema::hasTable('payment_informations')) {
            Schema::dropIfExists('payment_informations');
        }

        $this->dropColumnIfExists('wallets', 'added_by');
        $this->dropColumnIfExists('permissions', 'section');
        $this->dropColumnIfExists('orders', 'order_note');
        $this->dropColumnIfExists('orders', 'invoice_number');

        foreach ([
            'show_delivery_notes',
            'delivery_note_id',
            'show_warranty_note',
            'show_shipping_note',
            'shipping_note_id',
            'show_estimated_shipping_time',
            'promotional',
            'pos',
            'draft',
        ] as $column) {
            $this->dropColumnIfExists('products', $column);
        }
    }

    private function addProductColumns(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'draft')) {
                $table->boolean('draft')->default(false);
            }
            if (!Schema::hasColumn('products', 'pos')) {
                $table->boolean('pos')->default(false);
            }
            if (!Schema::hasColumn('products', 'promotional')) {
                $table->boolean('promotional')->default(false)->index();
            }
            if (!Schema::hasColumn('products', 'show_estimated_shipping_time')) {
                $table->boolean('show_estimated_shipping_time')->default(false);
            }
            if (!Schema::hasColumn('products', 'shipping_note_id')) {
                $table->unsignedBigInteger('shipping_note_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'show_shipping_note')) {
                $table->boolean('show_shipping_note')->default(false);
            }
            if (!Schema::hasColumn('products', 'show_warranty_note')) {
                $table->boolean('show_warranty_note')->default(false);
            }
            if (!Schema::hasColumn('products', 'delivery_note_id')) {
                $table->unsignedBigInteger('delivery_note_id')->nullable();
            }
            if (!Schema::hasColumn('products', 'show_delivery_notes')) {
                $table->boolean('show_delivery_notes')->default(false);
            }
        });
    }

    private function addOrderColumns(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'order_note')) {
                $table->text('order_note')->nullable();
            }
        });
    }

    private function addWalletColumns(): void
    {
        if (!Schema::hasTable('wallets')) {
            return;
        }

        Schema::table('wallets', function (Blueprint $table) {
            if (!Schema::hasColumn('wallets', 'added_by')) {
                $table->string('added_by')->default('customer')->index();
            }
        });
    }

    private function addPermissionColumns(): void
    {
        if (!Schema::hasTable('permissions') || Schema::hasColumn('permissions', 'section')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('section')->nullable()->index();
        });
    }

    private function createPaymentInformationTable(): void
    {
        if (Schema::hasTable('payment_informations')) {
            return;
        }

        Schema::create('payment_informations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('payment_type')->nullable();
            $table->string('payment_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('routing_number')->nullable();
            $table->text('payment_instruction')->nullable();
            $table->boolean('set_default')->default(false)->index();
            $table->timestamps();
        });
    }

    private function createAiTables(): void
    {
        if (!Schema::hasTable('ai_prompts')) {
            Schema::create('ai_prompts', function (Blueprint $table) {
                $table->id();
                $table->string('identifier')->unique();
                $table->string('type')->nullable();
                $table->longText('prompt');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ai_usage_logs')) {
            Schema::create('ai_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedInteger('prompt_tokens')->default(0);
                $table->unsignedInteger('completion_tokens')->default(0);
                $table->unsignedInteger('total_tokens')->default(0);
                $table->string('model')->nullable();
                $table->timestamps();
            });
        }
    }

    private function seedSettingsAndPermissions(): void
    {
        $now = now();

        if (Schema::hasTable('business_settings')) {
            foreach ($this->businessSettings() as $type => $value) {
                DB::table('business_settings')->updateOrInsert(
                    ['type' => $type],
                    ['value' => $value, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        if (Schema::hasTable('permissions')) {
            foreach ($this->permissions() as $name => $section) {
                DB::table('permissions')->updateOrInsert(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['section' => $section, 'updated_at' => $now, 'created_at' => $now]
                );
            }

            DB::table('permissions')
                ->where('name', 'set_category_wise_discount')
                ->update(['section' => 'promotion_and_offers', 'updated_at' => $now]);
        }

        if (Schema::hasTable('ai_prompts')) {
            DB::table('ai_prompts')->updateOrInsert(
                ['identifier' => 'product_add_edit_prompt'],
                [
                    'type' => 'product',
                    'prompt' => "Generate JSON only for product {product_name} in {language}. Include: {prompt_fields}. Do not include markdown fences.",
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    private function markExistingPromotionalProducts(): void
    {
        if (!Schema::hasTable('products') || !Schema::hasColumn('products', 'promotional')) {
            return;
        }

        if (Schema::hasColumn('products', 'todays_deal')) {
            DB::table('products')->where('todays_deal', 1)->update(['promotional' => 1]);
        }

        if (Schema::hasTable('flash_deal_products')) {
            $productIds = DB::table('flash_deal_products')->pluck('product_id')->filter()->unique()->values();
            if ($productIds->isNotEmpty()) {
                DB::table('products')->whereIn('id', $productIds)->update(['promotional' => 1]);
            }
        }

        if (Schema::hasTable('coupons') && Schema::hasColumn('coupons', 'details')) {
            $couponProductIds = DB::table('coupons')
                ->pluck('details')
                ->flatMap(function ($details) {
                    $decoded = json_decode($details, true);
                    return collect($decoded['product_ids'] ?? $decoded['product_id'] ?? [])
                        ->flatten()
                        ->filter(fn ($id) => is_numeric($id));
                })
                ->unique()
                ->values();

            if ($couponProductIds->isNotEmpty()) {
                DB::table('products')->whereIn('id', $couponProductIds)->update(['promotional' => 1]);
            }
        }
    }

    private function businessSettings(): array
    {
        return [
            'invoice_config' => json_encode([
                'invoice_title' => 'default',
                'company_name_and_address' => 'get_from_general_settings',
                'phone_email' => 'get_from_general_settings',
                'generate_invoice_number' => 1,
                'barcode_type' => 'code128',
                'barcode_encode' => 'order_code',
                'fields' => [],
            ]),
            'shipping_label' => json_encode([
                'label_size_preset' => '4x6',
                'barcode_type' => 'code128',
                'barcode_encode' => 'order_code',
                'fields' => [],
            ]),
            'thermal_printer' => json_encode([
                'generate_invoice_for_thermal_printer' => 0,
                'fields' => [],
            ]),
            'ai_activation' => '0',
            'gemini_model' => 'gemini-2.0-flash-lite',
            'seller_product_refund_approval' => 'admin_approval_required',
            'current_version' => '10.9.0',
            'facebook_pixel_capi' => '0',
        ];
    }

    private function permissions(): array
    {
        return [
            'manage_order' => 'orders',
            'can_download_and_print_shipping_label' => 'orders',
            'view_promotion_and_offers_dashboard' => 'promotion_and_offers',
            'view_promotional_product' => 'promotion_and_offers',
            'add_promotional_products' => 'promotion_and_offers',
            'remove_from_promotional' => 'promotion_and_offers',
            'remove_from_todays_deal' => 'promotion_and_offers',
            'add_todays_deal_products' => 'promotion_and_offers',
            'can_set_category_wise_discount' => 'promotion_and_offers',
            'customer_delete' => 'customer',
            'manage_ai_configuration' => 'configuration',
        ];
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($column) {
            $table->dropColumn($column);
        });
    }
};
