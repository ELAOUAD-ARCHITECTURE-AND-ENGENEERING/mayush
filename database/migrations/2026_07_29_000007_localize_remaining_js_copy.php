<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $translations = [
            'Sale ended' => 'La vente est terminée',
            'See Less' => 'Voir moins',
            'Edit' => 'Modifier',
            'Save' => 'Enregistrer',
            'If you disable a Country, all associated States, Cities, and Areas under that country will also be automatically disabled' => 'Si vous désactivez un pays, tous les états, villes et zones associés à ce pays seront également désactivés automatiquement',
            'If you disable a State, all associated Cities and Areas under that state will also be automatically disabled' => 'Si vous désactivez un état, toutes les villes et zones associées à cet état seront également désactivées automatiquement',
            'If you disable a City, all associated Areas under that city will also be automatically disabled.' => 'Si vous désactivez une ville, toutes les zones associées à cette ville seront également désactivées automatiquement.',
            'Are you sure you want to change the Recaptcha setting for' => 'Voulez-vous vraiment modifier le réglage reCAPTCHA pour',
            'Are you sure you want to change the Turnstile setting for' => 'Voulez-vous vraiment modifier le réglage Turnstile pour',
            'Are you sure?' => 'Êtes-vous sûr ?',
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
