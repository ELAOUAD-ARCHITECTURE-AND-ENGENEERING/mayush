<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_challenges', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->uuid('device_id');
            $table->string('type', 50); // 'device_verification', 'unusual_activity'
            $table->string('code_hash', 255);
            $table->string('method', 20)->default('email'); // 'email' or 'sms'
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'device_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_challenges');
    }
};
