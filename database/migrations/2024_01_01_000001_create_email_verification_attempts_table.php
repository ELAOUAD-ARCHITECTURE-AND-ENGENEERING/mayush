<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailVerificationAttemptsTable extends Migration
{
    public function up()
    {
        Schema::create('email_verification_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('ip_address', 45)->nullable();
            $table->string('attempt_type', 50)->default('request'); // 'request' | 'verify'
            $table->string('token')->nullable(); // the token sent
            $table->timestamp('token_expires_at')->nullable(); // 24h from created_at
            $table->boolean('used')->default(false); // single-use flag
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_verification_attempts');
    }
}
