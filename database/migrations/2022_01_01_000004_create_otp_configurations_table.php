<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOtpConfigurationsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('otp_configurations')) {
            Schema::create('otp_configurations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type')->nullable();
                $table->string('value')->nullable();
                $table->text('info')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('otp_configurations');
    }
}
