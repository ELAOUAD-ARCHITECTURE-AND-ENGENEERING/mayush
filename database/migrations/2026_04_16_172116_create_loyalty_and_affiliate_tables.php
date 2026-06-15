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
        if (!Schema::hasTable('club_points')) {
            Schema::create('club_points', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id');
                $table->double('points', 20, 2)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('club_point_details')) {
            Schema::create('club_point_details', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('club_point_id');
                $table->bigInteger('order_id');
                $table->bigInteger('product_id')->nullable();
                $table->double('points', 20, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('affiliate_configs')) {
            Schema::create('affiliate_configs', function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->longText('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('affiliate_users')) {
            Schema::create('affiliate_users', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id');
                $table->double('balance', 20, 2)->default(0);
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('affiliate_logs')) {
            Schema::create('affiliate_logs', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id');
                $table->bigInteger('order_id');
                $table->bigInteger('order_detail_id')->nullable();
                $table->double('amount', 20, 2);
                $table->integer('status')->default(0);
                $table->integer('viewed')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_points');
        Schema::dropIfExists('club_point_details');
        Schema::dropIfExists('affiliate_configs');
        Schema::dropIfExists('affiliate_users');
        Schema::dropIfExists('affiliate_logs');
    }
};
