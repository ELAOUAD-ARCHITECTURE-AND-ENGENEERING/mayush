<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id')->nullable(); // admin who acted
            $table->unsignedBigInteger('target_user_id')->nullable();            // user affected
            $table->string('action_type', 100);                      // e.g. 'manual_verification_request'
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['target_user_id', 'action_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
