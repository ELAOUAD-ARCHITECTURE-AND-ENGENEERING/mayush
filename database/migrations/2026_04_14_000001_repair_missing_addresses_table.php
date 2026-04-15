<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RepairMissingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('address', 500)->nullable();
                $table->integer('country_id')->nullable();
                $table->integer('state_id')->nullable();
                $table->integer('city_id')->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->integer('area_id')->nullable();
                $table->string('phone', 30)->nullable();
                $table->tinyInteger('set_default')->default(0);
                $table->tinyInteger('set_billing')->default(0);
                $table->double('longitude', 10, 7)->nullable();
                $table->double('latitude', 10, 7)->nullable();
                $table->timestamps();
                
                // Indexes for performance
                $table->index('user_id');
                $table->index('set_default');
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
        Schema::dropIfExists('addresses');
    }
}
