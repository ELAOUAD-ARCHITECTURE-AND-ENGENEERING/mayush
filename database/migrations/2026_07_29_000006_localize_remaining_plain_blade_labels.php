<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $translations = [
            'Confirmation' => 'Confirmation',
            'Addon' => 'Extension',
            'HRS' => 'HEU',
            'MIN' => 'MIN',
            'SEC' => 'SEC',
            'or' => 'ou',
            'Installer' => 'Installateur',
            'O&M' => 'O&M',
            'PHP version' => 'Version PHP',
            'Classic' => 'Classique',
            'Metro' => 'Metro',
            'Minima' => 'Minima',
            'Filter' => 'Filtrer',
            'PTS' => 'PTS',
            'All Discounted' => 'Tous les produits remisés',
            'DRAFT' => 'BROUILLON',
            'Text' => 'Texte',
            'INVOICE' => 'FACTURE',
            'Delivery Type' => 'Type de livraison',
            'Home' => 'À domicile',
            'Pickup' => 'Retrait',
            'Carrier' => 'Transporteur',
            'N/A' => 'N/D',
            'PAID' => 'PAYÉ',
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
