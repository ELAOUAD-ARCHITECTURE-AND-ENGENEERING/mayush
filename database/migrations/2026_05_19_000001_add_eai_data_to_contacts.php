<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contacts') && !Schema::hasColumn('contacts', 'eai_data')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->json('eai_data')->nullable()->after('content');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'eai_data')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropColumn('eai_data');
            });
        }
    }
};
