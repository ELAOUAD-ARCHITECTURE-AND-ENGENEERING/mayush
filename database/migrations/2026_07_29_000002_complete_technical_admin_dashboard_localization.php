<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add strings discovered after the first technical dashboard localization
     * pass, including dynamic security event prefixes and status values.
     */
    public function up(): void
    {
        $translations = [
            'Email' => 'E-mail',
            'Social' => 'Réseaux sociaux',
            'Search' => 'Recherche',
            'Inactive' => 'Inactif',
            'Error (Redis Down)' => 'Erreur (Redis indisponible)',
            'Danger' => 'Critique',
            'User logged in:' => 'Utilisateur connecté :',
            'User logged out:' => 'Utilisateur déconnecté :',
            'Failed login attempt for email:' => 'Échec de connexion pour l’e-mail :',
            'Infected file rejected:' => 'Fichier infecté rejeté :',
            'Unauthorized access attempt to' => 'Tentative d’accès non autorisé à',
        ];

        foreach ($translations as $source => $french) {
            $key = preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', strtolower($source)));

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
        // Translation rows are shared application data; leave them intact on rollback.
    }
};
