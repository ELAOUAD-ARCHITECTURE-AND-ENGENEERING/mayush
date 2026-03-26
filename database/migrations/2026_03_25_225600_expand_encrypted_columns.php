<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpandEncryptedColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('phone')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('postal_code')->nullable()->change();
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->text('bank_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->change();
            $table->string('address', 300)->nullable()->change();
            $table->string('postal_code', 20)->nullable()->change();
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->string('bank_name', 255)->nullable()->change();
        });
    }
}
