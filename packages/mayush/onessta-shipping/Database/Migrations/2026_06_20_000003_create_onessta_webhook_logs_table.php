<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('onessta_webhook_logs')) {
            Schema::create('onessta_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->string('event_type', 100)->index();
                $table->string('header_api_key')->nullable();
                $table->string('header_signature')->nullable();
                $table->string('header_event')->nullable();
                $table->longText('payload');
                $table->boolean('signature_valid')->nullable();
                $table->boolean('processed')->default(false)->index();
                $table->text('error_message')->nullable();
                $table->unsignedBigInteger('onessta_shipment_id')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->foreign('onessta_shipment_id', 'fk_webhook_shipment')
                    ->references('id')->on('onessta_shipments')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('onessta_webhook_logs');
    }
};
