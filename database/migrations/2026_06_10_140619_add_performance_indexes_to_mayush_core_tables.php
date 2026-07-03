<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. business_settings
        Schema::table('business_settings', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('business_settings'))->pluck('name');
            if (!$indexes->contains('business_settings_type_index')) {
                $table->index('type', 'business_settings_type_index');
            }
        });

        // 2. product_stocks
        Schema::table('product_stocks', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('product_stocks'))->pluck('name');
            if (!$indexes->contains('product_stocks_product_id_index')) {
                $table->index('product_id', 'product_stocks_product_id_index');
            }
        });

        // 3. orders
        Schema::table('orders', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('orders'))->pluck('name');
            if (!$indexes->contains('orders_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at'], 'orders_user_id_created_at_index');
            }
            if (!$indexes->contains('orders_seller_id_created_at_index')) {
                $table->index(['seller_id', 'created_at'], 'orders_seller_id_created_at_index');
            }
            if (!$indexes->contains('orders_seller_id_payment_status_index')) {
                $table->index(['seller_id', 'payment_status'], 'orders_seller_id_payment_status_index');
            }
            if (!$indexes->contains('orders_status_dates_idx')) {
                $table->index(['payment_status', 'delivery_status', 'created_at'], 'orders_status_dates_idx');
            }
            if (!$indexes->contains('orders_combined_order_id_payment_status_index')) {
                $table->index(['combined_order_id', 'payment_status'], 'orders_combined_order_id_payment_status_index');
            }
        });

        // 4. order_details
        Schema::table('order_details', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('order_details'))->pluck('name');
            if (!$indexes->contains('order_details_order_id_index')) {
                $table->index('order_id', 'order_details_order_id_index');
            }
            if (!$indexes->contains('order_details_product_id_index')) {
                $table->index('product_id', 'order_details_product_id_index');
            }
        });

        // 5. products
        Schema::table('products', function (Blueprint $table) {
            $indexes = collect(Schema::getIndexes('products'))->pluck('name');
            if (!$indexes->contains('products_slug_index')) {
                $table->index('slug', 'products_slug_index');
            }
            if (!$indexes->contains('products_user_id_published_approved_index')) {
                $table->index(['user_id', 'published', 'approved'], 'products_user_id_published_approved_index');
            }
            if (!$indexes->contains('products_brand_id_published_approved_index')) {
                $table->index(['brand_id', 'published', 'approved'], 'products_brand_id_published_approved_index');
            }
        });

        // 6. payment_attempts
        if (Schema::hasTable('payment_attempts')) {
            Schema::table('payment_attempts', function (Blueprint $table) {
                $indexes = collect(Schema::getIndexes('payment_attempts'))->pluck('name');
                if (!$indexes->contains('payment_attempts_status_created_at_index')) {
                    $table->index(['status', 'created_at'], 'payment_attempts_status_created_at_index');
                }
                if (!$indexes->contains('payment_attempts_gateway_reference_index')) {
                    $table->index('gateway_reference', 'payment_attempts_gateway_reference_index');
                }
                if (!$indexes->contains('payment_attempts_merchant_reference_index')) {
                    $table->index('merchant_reference', 'payment_attempts_merchant_reference_index');
                }
            });
        }

        // 7. cmi_callback_logs
        if (Schema::hasTable('cmi_callback_logs')) {
            Schema::table('cmi_callback_logs', function (Blueprint $table) {
                $indexes = collect(Schema::getIndexes('cmi_callback_logs'))->pluck('name');
                if (!$indexes->contains('cmi_callback_logs_status_dates_idx')) {
                    $table->index(['processing_status', 'created_at'], 'cmi_callback_logs_status_dates_idx');
                }
                if (!$indexes->contains('cmi_callback_logs_merchant_ref_status_idx')) {
                    $table->index(['merchant_reference', 'processing_status'], 'cmi_callback_logs_merchant_ref_status_idx');
                }
                if (!$indexes->contains('cmi_callback_logs_payload_hash_index')) {
                    $table->index('payload_hash', 'cmi_callback_logs_payload_hash_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropIndex('business_settings_type_index');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropIndex('product_stocks_product_id_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_id_created_at_index');
            $table->dropIndex('orders_seller_id_created_at_index');
            $table->dropIndex('orders_seller_id_payment_status_index');
            $table->dropIndex('orders_status_dates_idx');
            $table->dropIndex('orders_combined_order_id_payment_status_index');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('order_details_order_id_index');
            $table->dropIndex('order_details_product_id_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_slug_index');
            $table->dropIndex('products_user_id_published_approved_index');
            $table->dropIndex('products_brand_id_published_approved_index');
        });

        if (Schema::hasTable('payment_attempts')) {
            Schema::table('payment_attempts', function (Blueprint $table) {
                $table->dropIndex('payment_attempts_status_created_at_index');
                $table->dropIndex('payment_attempts_gateway_reference_index');
                $table->dropIndex('payment_attempts_merchant_reference_index');
            });
        }

        if (Schema::hasTable('cmi_callback_logs')) {
            Schema::table('cmi_callback_logs', function (Blueprint $table) {
                $table->dropIndex('cmi_callback_logs_status_dates_idx');
                $table->dropIndex('cmi_callback_logs_merchant_ref_status_idx');
                $table->dropIndex('cmi_callback_logs_payload_hash_index');
            });
        }
    }
};
