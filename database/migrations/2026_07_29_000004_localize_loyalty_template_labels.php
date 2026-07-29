<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $translations = [
            'Back to dashboard' => 'Retour au tableau de bord',
            'Dynamic' => 'Dynamique',
            'Fixed:' => 'Fixe :',
            'points' => 'points',
            'None' => 'Aucun',
            'Dynamic: Percentage of Unit Price' => 'Dynamique : pourcentage du prix unitaire',
        ];

        foreach ($translations as $source => $french) {
            $key = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '_', $source), '_'));

            DB::table('translations')->updateOrInsert(
                ['lang' => 'en', 'lang_key' => $key],
                ['lang_value' => $source, 'updated_at' => now(), 'created_at' => now()]
            );
            DB::table('translations')->updateOrInsert(
                ['lang' => 'fr', 'lang_key' => $key],
                ['lang_value' => $french, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        Cache::forget('translations-en');
        Cache::forget('translations-fr');
    }

    public function down(): void
    {
        // Shared translation rows are intentionally retained on rollback.
    }
};
