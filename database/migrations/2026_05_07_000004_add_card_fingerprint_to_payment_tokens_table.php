<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_tokens') && !Schema::hasColumn('payment_tokens', 'card_fingerprint')) {
            Schema::table('payment_tokens', function (Blueprint $table) {
                $table->string('card_fingerprint', 64)->nullable()->after('card_brand');
                $table->index(['user_id', 'gateway', 'card_fingerprint'], 'payment_tokens_user_gateway_fingerprint_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_tokens') && Schema::hasColumn('payment_tokens', 'card_fingerprint')) {
            Schema::table('payment_tokens', function (Blueprint $table) {
                $table->dropIndex('payment_tokens_user_gateway_fingerprint_index');
                $table->dropColumn('card_fingerprint');
            });
        }
    }
};
