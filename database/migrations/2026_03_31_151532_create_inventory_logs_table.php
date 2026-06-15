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
        if (!Schema::hasTable('inventory_logs')) {
            Schema::create('inventory_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('user_id')->nullable()->comment('Who made the change');
                $table->integer('quantity_delta')->comment('Amount added or removed');
                $table->integer('previous_stock');
                $table->integer('current_stock');
                $table->string('reason')->comment('order, restock, manual, etc');
                $table->unsignedBigInteger('order_id')->nullable();
                $table->timestamps();

                $table->index('product_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
