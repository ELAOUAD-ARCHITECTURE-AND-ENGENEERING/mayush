<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HardenUsersTableSchema extends Migration
{
    /**
     * Run the migrations.
     * Ensures the users table has all necessary columns for loyalty and wallet functionality, 
     * providing a stable baseline for integration tests.
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'balance')) {
                    $table->double('balance', 20, 2)->default(0)->after('email');
                }
                if (!Schema::hasColumn('users', 'annual_spend')) {
                    $table->decimal('annual_spend', 12, 2)->default(0)->after('balance');
                }
                if (!Schema::hasColumn('users', 'customer_package_id')) {
                    $table->integer('customer_package_id')->nullable()->after('annual_spend');
                }
                if (!Schema::hasColumn('users', 'remaining_uploads')) {
                    $table->integer('remaining_uploads')->default(0);
                }
            });
        } else {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('user_type', 20)->default('customer');
                $table->string('avatar')->nullable();
                $table->string('avatar_original')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->string('country')->nullable();
                $table->string('city')->nullable();
                $table->string('postal_code')->nullable();
                $table->double('balance', 20, 2)->default(0);
                $table->decimal('annual_spend', 12, 2)->default(0);
                $table->integer('customer_package_id')->nullable();
                $table->integer('remaining_uploads')->default(0);
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Non-destructive migration for hardening
    }
}
