<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('onessta_tracking_events')) {
            Schema::create('onessta_tracking_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('onessta_shipment_id')->constrained('onessta_shipments')->onDelete('cascade');
                $table->string('status', 100);
                $table->string('name')->nullable();
                $table->timestamp('created_at_remote')->nullable();
                $table->timestamp('new_date')->nullable();
                $table->json('raw_payload')->nullable();
                $table->timestamps();

                $table->unique(['onessta_shipment_id', 'status', 'created_at_remote'], 'unique_tracking_event');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('onessta_tracking_events');
    }
};
