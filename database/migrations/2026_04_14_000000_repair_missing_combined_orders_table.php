<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RepairMissingCombinedOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('combined_orders')) {
            Schema::create('combined_orders', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->longText('shipping_address')->nullable();
                $table->double('shipping_cost', 20, 2)->default(0.00);
                $table->double('grand_total', 20, 2)->default(0.00);
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
        Schema::dropIfExists('combined_orders');
    }
}
