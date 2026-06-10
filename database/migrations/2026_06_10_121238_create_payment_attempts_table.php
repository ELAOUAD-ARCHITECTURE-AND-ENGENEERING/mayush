<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('combined_order_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('gateway')->default('cmi');
            $table->string('gateway_reference')->nullable();
            $table->string('merchant_reference')->nullable();
            $table->decimal('amount', 20, 2)->nullable();
            $table->string('currency', 10)->default('MAD');
            $table->string('status')->default('initiated');
            $table->string('request_payload_hash')->nullable();
            $table->string('response_payload_hash')->nullable();
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('gateway');
            $table->index('gateway_reference');
            $table->index('merchant_reference');
            $table->index('combined_order_id');
            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
