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
        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->integer('owner_id')->nullable();
                $table->integer('user_id')->nullable();
                $table->string('temp_user_id')->nullable();
                $table->integer('address_id')->nullable();
                $table->integer('billing_address')->nullable();
                $table->integer('product_id')->nullable();
                $table->string('variation')->nullable();
                $table->double('price')->nullable();
                $table->double('tax')->nullable();
                $table->double('shipping_cost')->nullable();
                $table->double('discount')->nullable();
                $table->string('product_referral_code')->nullable();
                $table->string('coupon_code')->nullable();
                $table->boolean('coupon_applied')->default(false);
                $table->string('shipping_type')->nullable();
                $table->integer('pickup_point')->nullable();
                $table->integer('carrier_id')->nullable();
                $table->integer('quantity')->default(1);
                $table->integer('status')->default(0);
                $table->timestamps();
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
        Schema::dropIfExists('carts');
    }
};
