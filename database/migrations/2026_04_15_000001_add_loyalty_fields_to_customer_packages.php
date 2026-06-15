<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoyaltyFieldsToCustomerPackages extends Migration
{
    /**
     * Run the migrations.
     * Adds loyalty tier configuration fields to customer_packages table.
     * These allow each package to act as a loyalty tier with auto-upgrade logic.
     */
    public function up()
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_packages', 'min_spend')) {
                $table->decimal('min_spend', 12, 2)->nullable()->after('amount')
                    ->comment('Minimum annual spend in system currency to qualify for this tier (NULL = no minimum)');
            }
            if (!Schema::hasColumn('customer_packages', 'loyalty_multiplier')) {
                $table->decimal('loyalty_multiplier', 5, 2)->default(1.00)->after('min_spend')
                    ->comment('Club point multiplier for this tier (1.0 = standard, 1.5 = 50% bonus)');
            }
            if (!Schema::hasColumn('customer_packages', 'tier_level')) {
                $table->tinyInteger('tier_level')->default(0)->after('loyalty_multiplier')
                    ->comment('Loyalty tier rank: 0=Basic, 1=Silver, 2=Gold, 3=Platinum');
            }
            if (!Schema::hasColumn('customer_packages', 'is_loyalty_tier')) {
                $table->boolean('is_loyalty_tier')->default(false)->after('tier_level')
                    ->comment('If true, this package is used as an automatic loyalty tier (not purchasable)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('customer_packages', function (Blueprint $table) {
            $table->dropColumn(['min_spend', 'loyalty_multiplier', 'tier_level', 'is_loyalty_tier']);
        });
    }
}
