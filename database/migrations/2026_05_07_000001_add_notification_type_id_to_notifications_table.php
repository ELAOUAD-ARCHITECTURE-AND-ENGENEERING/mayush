<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications') || Schema::hasColumn('notifications', 'notification_type_id')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('notification_type_id')->nullable()->after('id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('notifications') || !Schema::hasColumn('notifications', 'notification_type_id')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('notification_type_id');
        });
    }
};
