<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Missing Tables for Testing
        if (!Schema::hasTable('notification_types')) {
            Schema::create('notification_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type')->nullable();
                $table->string('name')->nullable();
                $table->string('image')->nullable();
                $table->string('default_text')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('commission_histories')) {
            Schema::create('commission_histories', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id');
                $table->integer('order_detail_id')->nullable();
                $table->integer('seller_id');
                $table->double('admin_commission', 20, 2);
                $table->double('seller_earning', 20, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('order_details')) {
            Schema::create('order_details', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('order_id');
                $table->integer('seller_id')->nullable();
                $table->integer('product_id');
                $table->string('variation', 255)->nullable();
                $table->double('price', 20, 2)->default(0);
                $table->double('tax', 20, 2)->default(0);
                $table->double('shipping_cost', 20, 2)->default(0);
                $table->integer('quantity')->default(1);
                $table->string('payment_status', 20)->default('unpaid');
                $table->string('delivery_status', 20)->default('pending');
                $table->string('shipping_type', 20)->nullable();
                $table->integer('pickup_point_id')->nullable();
                $table->string('product_referral_code', 255)->nullable();
                $table->double('earn_point', 20, 2)->default(0);
                $table->integer('reviewed')->default(0);
                $table->integer('refund_days')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shops')) {
            Schema::create('shops', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('name', 200)->nullable();
                $table->string('logo', 200)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('seller_packages')) {
            Schema::create('seller_packages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 200)->nullable();
                $table->double('amount', 20, 2)->default(0);
                $table->integer('product_upload_limit')->default(0);
                $table->integer('duration')->default(0);
                $table->string('logo', 200)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_translations')) {
            Schema::create('product_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->string('name', 200)->nullable();
                $table->string('unit', 20)->nullable();
                $table->longText('description')->nullable();
                $table->string('lang', 10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('category_translations')) {
            Schema::create('category_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id');
                $table->string('name', 200)->nullable();
                $table->string('lang', 10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('brand_translations')) {
            Schema::create('brand_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('brand_id');
                $table->string('name', 200)->nullable();
                $table->string('lang', 10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->integer('category_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_stocks')) {
            Schema::create('product_stocks', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->string('variant', 255)->nullable();
                $table->string('sku', 255)->nullable();
                $table->double('price', 20, 2)->default(0);
                $table->integer('qty')->default(0);
                $table->string('image', 1000)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_taxes')) {
            Schema::create('product_taxes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->double('tax', 20, 2)->default(0);
                $table->string('tax_type', 10)->default('flat');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tickets')) {
            Schema::create('tickets', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('subject', 255)->nullable();
                $table->longText('details')->nullable();
                $table->text('files')->nullable();
                $table->string('status', 100)->default('pending');
                $table->string('code', 100)->nullable();
                $table->integer('viewed')->default(0);
                $table->integer('client_viewed')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('sender_id');
                $table->integer('receiver_id');
                $table->string('title', 1000)->nullable();
                $table->integer('sender_viewed')->default(0);
                $table->integer('receiver_viewed')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('messages')) {
            Schema::create('messages', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('conversation_id');
                $table->integer('user_id');
                $table->text('message');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('type');
                $table->string('notifiable_type');
                $table->unsignedBigInteger('notifiable_id');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        }

        if (!Schema::hasTable('preorders')) {
            Schema::create('preorders', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('product_id');
                $table->integer('product_owner_id')->nullable();
                $table->string('code', 100)->nullable();
                $table->integer('prepayment_confirm_status')->default(0);
                $table->integer('request_preorder_status')->default(0);
                $table->integer('final_order_status')->default(0);
                $table->integer('is_viewed')->default(0);
                $table->timestamp('request_preorder_time')->nullable();
                $table->timestamp('prepayment_confirmation_time')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('preorder_products')) {
            Schema::create('preorder_products', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();
                $table->integer('category_id')->nullable();
                $table->integer('brand_id')->nullable();
                $table->string('name', 200)->nullable();
                $table->integer('is_available')->default(1);
                $table->integer('is_prepayment')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ticket_replies')) {
            Schema::create('ticket_replies', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('ticket_id');
                $table->integer('user_id');
                $table->text('reply')->nullable();
                $table->text('files')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('manual_payment_methods')) {
            Schema::create('manual_payment_methods', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 200)->nullable();
                $table->string('name', 200)->nullable();
                $table->string('bank_name', 200)->nullable();
                $table->string('account_number', 255)->nullable();
                $table->string('account_name', 255)->nullable();
                $table->string('branch', 255)->nullable();
                $table->string('account_type', 255)->nullable();
                $table->string('routing_number', 255)->nullable();
                $table->string('swift_code', 255)->nullable();
                $table->string('logo', 255)->nullable();
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('symbol', 100);
                $table->double('exchange_rate', 20, 2);
                $table->string('code', 100);
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 2);
                $table->string('name', 100);
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->increments('id');
                $table->string('identifier', 100)->nullable();
                $table->string('subject', 200)->nullable();
                $table->text('content')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'combined_order_id')) {
                $table->integer('combined_order_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('orders', 'seller_id')) {
                $table->integer('seller_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('seller_id');
            }
            if (!Schema::hasColumn('orders', 'billing_address')) {
                $table->text('billing_address')->nullable()->after('shipping_address');
            }
            if (!Schema::hasColumn('orders', 'payment_type')) {
                $table->string('payment_type', 20)->nullable()->after('billing_address');
            }
            if (!Schema::hasColumn('orders', 'payment_details')) {
                $table->text('payment_details')->nullable()->after('payment_type');
            }
            if (!Schema::hasColumn('orders', 'additional_info')) {
                $table->text('additional_info')->nullable()->after('payment_details');
            }
            if (!Schema::hasColumn('orders', 'code')) {
                $table->string('code', 100)->nullable()->after('id');
            }
            if (!Schema::hasColumn('orders', 'date')) {
                $table->integer('date')->nullable()->after('code');
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->after('date');
            }
            if (!Schema::hasColumn('orders', 'delivery_status')) {
                $table->string('delivery_status', 20)->default('pending')->after('payment_status');
            }
            if (!Schema::hasColumn('orders', 'grand_total')) {
                $table->double('grand_total', 20, 2)->default(0)->after('delivery_status');
            }
            if (!Schema::hasColumn('orders', 'viewed')) {
                $table->integer('viewed')->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('orders', 'delivery_viewed')) {
                $table->integer('delivery_viewed')->default(0)->after('viewed');
            }
            if (!Schema::hasColumn('orders', 'payment_status_viewed')) {
                $table->integer('payment_status_viewed')->default(0)->after('delivery_viewed');
            }
            if (!Schema::hasColumn('orders', 'commission_calculated')) {
                $table->integer('commission_calculated')->default(0)->after('payment_status_viewed');
            }
            if (!Schema::hasColumn('orders', 'shipping_method')) {
                $table->string('shipping_method', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'shiprocket_order_id')) {
                $table->string('shiprocket_order_id', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'shiprocket_status')) {
                $table->string('shiprocket_status', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'steadfast_consignment_id')) {
                $table->string('steadfast_consignment_id', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'steadfast_status')) {
                $table->string('steadfast_status', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'pathao_consignment_id')) {
                $table->string('pathao_consignment_id', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'pathao_status')) {
                $table->string('pathao_status', 100)->nullable();
            }
            if (!Schema::hasColumn('orders', 'order_from')) {
                $table->string('order_from', 20)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_types');
        Schema::dropIfExists('commission_histories');
        Schema::dropIfExists('order_details');
        Schema::dropIfExists('shops');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'combined_order_id', 'seller_id', 'shipping_address', 'billing_address', 
                'payment_type', 'payment_details', 'additional_info', 'code', 'date', 
                'payment_status', 'delivery_status', 'grand_total', 'viewed',
                'payment_status_viewed', 'delivery_viewed', 'commission_calculated',
                'shipping_method', 'shiprocket_order_id', 'shiprocket_status',
                'steadfast_consignment_id', 'steadfast_status',
                'pathao_consignment_id', 'pathao_status', 'order_from'
            ]);
        });
    }
};
