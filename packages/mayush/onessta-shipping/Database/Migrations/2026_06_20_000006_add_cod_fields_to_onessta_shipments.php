<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onessta_shipments', function (Blueprint $table) {
            $table->boolean('is_cod')->default(false)->after('replace');
            $table->string('payment_situation', 50)->nullable()->after('is_cod');
        });
    }

    public function down(): void
    {
        Schema::table('onessta_shipments', function (Blueprint $table) {
            $table->dropColumn(['is_cod', 'payment_situation']);
        });
    }
};
