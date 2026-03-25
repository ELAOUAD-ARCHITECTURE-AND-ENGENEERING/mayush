<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('seller_id')->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('order_detail_id')->nullable();
            $table->text('reason')->nullable();
            $table->double('refund_amount', 20, 2)->default(0);
            $table->integer('seller_approval')->default(0);
            $table->integer('admin_approval')->default(0);
            $table->integer('admin_seen')->default(0);
            $table->integer('refund_status')->default(0); // 0=pending, 1=approved, 2=rejected
            $table->text('admin_note')->nullable();
            $table->text('seller_note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('refund_requests');
    }
}
