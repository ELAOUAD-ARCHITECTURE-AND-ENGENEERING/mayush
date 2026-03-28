<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elite_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('elite_subscriptions', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('admin_notes');
            }
            if (!Schema::hasColumn('elite_subscriptions', 'payment_method')) {
                $table->string('payment_method')->nullable()->default('cmi')->after('transaction_id');
            }
            if (!Schema::hasColumn('elite_subscriptions', 'payment_details')) {
                $table->json('payment_details')->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('elite_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['transaction_id', 'payment_method', 'payment_details']);
        });
    }
};
