<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoyaltyTierToUsers extends Migration
{
    /**
     * Run the migrations.
     * Adds annual_spend to users table for automatic tier evaluation.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'annual_spend')) {
                $table->decimal('annual_spend', 12, 2)->default(0)->after('balance')
                    ->comment('Rolling 12-month spend in system base currency, used for loyalty tier recalculation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('annual_spend');
        });
    }
}
