<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cmi_callback_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->default('cmi');
            $table->unsignedBigInteger('payment_attempt_id')->nullable();
            $table->unsignedBigInteger('combined_order_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('merchant_reference')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->string('payload_hash')->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->boolean('is_duplicate')->default(false);
            $table->string('processing_status')->default('received');
            $table->json('raw_payload')->nullable();
            $table->json('normalized_payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('payload_hash');
            $table->index('merchant_reference');
            $table->index('gateway_reference');
            $table->index('processing_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cmi_callback_logs');
    }
};
