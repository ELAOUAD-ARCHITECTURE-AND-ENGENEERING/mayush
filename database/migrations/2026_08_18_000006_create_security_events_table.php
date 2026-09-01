<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('event_type', 40)->index(); // login_success, login_failed, password_change, device_verified
            $table->string('ip_address', 45);
            $table->string('device_id', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_country', 2)->nullable();   // ISO 3166-1 alpha-2, populated async
            $table->string('ip_city', 100)->nullable();
            $table->boolean('flagged')->default(false);     // true when anomaly detected
            $table->string('flag_reason', 255)->nullable(); // e.g. 'new_country', 'new_ip_range'
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'event_type', 'created_at']);
            $table->index(['user_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
