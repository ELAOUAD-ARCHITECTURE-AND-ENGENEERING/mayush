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
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->text('phone')->nullable()->change();
            }
            if (Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->change();
            }
            if (Schema::hasColumn('users', 'postal_code')) {
                $table->text('postal_code')->nullable()->change();
            }
        });

        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'bank_name')) {
                $table->text('bank_name')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

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
