<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('code')->nullable();
                $table->integer('status')->default(1);
                $table->integer('zone_id')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('country_id');
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('state_id')->nullable();
                $table->integer('country_id')->nullable();
                $table->string('cost')->nullable();
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
    }
};
