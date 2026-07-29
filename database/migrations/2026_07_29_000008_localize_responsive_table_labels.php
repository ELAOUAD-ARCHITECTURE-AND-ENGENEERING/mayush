<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $translations = [
            'Name' => 'Nom',
            'Email Address' => 'Adresse e-mail',
            'Phone' => 'Téléphone',
            'Package' => 'Forfait',
            'Wallet Balance' => 'Solde du portefeuille',
            'Verification Status' => 'Statut de vérification',
            'Options' => 'Options',
            'code' => 'Code',
            'Type' => 'Type',
            'Start Date' => 'Date de début',
            'End Date' => 'Date de fin',
            'Validation Days' => 'Jours de validation',
            'Status' => 'Statut',
            'Banner' => 'Bannière',
            'Title' => 'Titre',
            'Featured' => 'Mis en avant',
            'Thumb' => 'Vignette',
            'Owner Category' => 'Catégorie propriétaire',
            'Ratings' => 'Évaluations',
            'Price Details' => 'Détails du prix',
            'TodaysDeal' => 'Offre du jour',
            'Order-Code' => 'Code de commande',
            'OrderCount' => 'Nombre de commandes',
            'Customer' => 'Client',
            'Owner' => 'Propriétaire',
            'Delivery Status' => 'Statut de livraison',
            'Payment method' => 'Mode de paiement',
            'Payment Status' => 'Statut du paiement',
            'Refund' => 'Remboursement',
            'Date & Time' => 'Date et heure',
            'User' => 'Utilisateur',
            'Model' => 'Modèle',
            'Prompt Tokens' => 'Jetons du prompt',
            'Completion Tokens' => 'Jetons de complétion',
            'Total Tokens' => 'Total des jetons',
            'Info' => 'Informations',
            'Stock' => 'Stock',
            'Published' => 'Publié',
            'Approved' => 'Approuvé',
            'Icon' => 'Icône',
            'Parent' => 'Parent',
            'Inhouse' => 'Interne',
            'Seller' => 'Vendeur',
            'Refund Request Time(Days)' => 'Délai de demande de remboursement (jours)',
            'Discount' => 'Remise',
            'Date Range' => 'Plage de dates',
            'Courier Type' => 'Type de coursier',
            'Length' => 'Longueur',
            'Breadth' => 'Largeur',
            'Height' => 'Hauteur',
            'Address Nickname' => 'Surnom de l’adresse',
            'Categories' => 'Catégories',
            'Parent Category' => 'Catégorie parente',
            'Order Level' => 'Niveau de commande',
            'Level' => 'Niveau',
            'Hot Category' => 'Catégorie populaire',
            'Commission' => 'Commission',
            'Logo' => 'Logo',
            'Qty Products' => 'Qté de produits',
            'Created' => 'Créé',
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
