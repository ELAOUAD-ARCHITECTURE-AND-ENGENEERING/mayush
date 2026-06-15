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
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (!Schema::hasColumn('order_details', 'coupon_discount')) {
                    $table->double('coupon_discount', 20, 2)->nullable()->default(0.00);
                }
            });
        }
        
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'shipping_type')) {
                    $table->string('shipping_type')->nullable();
                }
                if (!Schema::hasColumn('orders', 'pickup_point_id')) {
                    $table->integer('pickup_point_id')->nullable();
                }
                if (!Schema::hasColumn('orders', 'carrier_id')) {
                    $table->integer('carrier_id')->nullable();
                }
                if (!Schema::hasColumn('orders', 'delivery_viewed')) {
                    $table->integer('delivery_viewed')->default(0);
                }
                if (!Schema::hasColumn('orders', 'payment_status_viewed')) {
                    $table->integer('payment_status_viewed')->default(0);
                }
                if (!Schema::hasColumn('orders', 'commission_calculated')) {
                    $table->integer('commission_calculated')->default(0);
                }
                if (!Schema::hasColumn('orders', 'manual_payment')) {
                    $table->integer('manual_payment')->default(0);
                }
                if (!Schema::hasColumn('orders', 'manual_payment_data')) {
                    $table->text('manual_payment_data')->nullable();
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
        //
    }
};
