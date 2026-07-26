<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('business_settings')) {
            return;
        }

        $existing = DB::table('business_settings')->where('type', 'openrouter_model')->value('value');
        $legacy = DB::table('business_settings')->where('type', 'gemini_model')->value('value');
        $model = filled($existing) ? $existing : ($legacy ?: config('services.openrouter.model', 'openrouter/free'));

        DB::table('business_settings')->updateOrInsert(
            ['type' => 'openrouter_model'],
            ['value' => $model, 'updated_at' => now(), 'created_at' => now()]
        );

        // Keep the legacy value for rollback/audit purposes. Runtime code no
        // longer reads it; deleting settings here would lose an administrator
        // value and make a rollback destructive.
    }

    public function down(): void
    {
        if (!Schema::hasTable('business_settings')) {
            return;
        }

        $model = DB::table('business_settings')->where('type', 'openrouter_model')->value('value');
        if (filled($model)) {
            DB::table('business_settings')->updateOrInsert(
                ['type' => 'gemini_model'],
                ['value' => $model, 'updated_at' => now(), 'created_at' => now()]
            );
        }
        DB::table('business_settings')->where('type', 'openrouter_model')->delete();
    }
};
