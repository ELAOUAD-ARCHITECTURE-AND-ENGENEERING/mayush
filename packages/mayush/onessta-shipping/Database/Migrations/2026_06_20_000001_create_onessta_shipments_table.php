<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('onessta_shipments')) {
            Schema::create('onessta_shipments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable()->index();
                $table->string('external_id')->nullable();
                $table->string('code')->unique();
                $table->string('receiver');
                $table->string('phone', 50);
                $table->text('address');
                $table->unsignedBigInteger('city_id')->nullable()->index();
                $table->string('city_name')->nullable();
                $table->unsignedInteger('remote_city_id')->nullable()->index();
                $table->unsignedInteger('pickup_city_id')->nullable();
                $table->string('pickup_city_name')->nullable();
                $table->decimal('price', 12, 2)->default(0);
                $table->text('sku')->nullable();
                $table->text('note')->nullable();
                $table->string('product_nature', 100)->nullable();
                $table->boolean('can_open')->default(false);
                $table->boolean('replace')->default(false);
                $table->string('status', 100)->nullable()->index();
                $table->string('status_second', 100)->nullable();
                $table->string('situation', 100)->nullable();
                $table->text('last_status_comment')->nullable();
                $table->timestamp('reported_date')->nullable();
                $table->json('raw_request')->nullable();
                $table->json('raw_response')->nullable();
                $table->timestamp('created_at_remote')->nullable();
                $table->timestamp('updated_at_remote')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();

                $table->index('code', 'idx_code');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('onessta_shipments');
    }
};
