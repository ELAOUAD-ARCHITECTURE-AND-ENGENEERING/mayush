<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BladeTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $translations = [
            // General UI & Common Actions
            ['en', 'select_file', 'Select File'],
            ['fr', 'select_file', 'Sélectionner un fichier'],
            ['en', 'upload_new', 'Upload New'],
            ['fr', 'upload_new', 'Télécharger un nouveau'],
            ['en', 'sort_by_newest', 'Sort by newest'],
            ['fr', 'sort_by_newest', 'Trier par plus récent'],
            ['en', 'sort_by_oldest', 'Sort by oldest'],
            ['fr', 'sort_by_oldest', 'Trier par plus ancien'],
            ['en', 'sort_by_smallest', 'Sort by smallest'],
            ['fr', 'sort_by_smallest', 'Trier par plus petit'],
            ['en', 'sort_by_largest', 'Sort by largest'],
            ['fr', 'sort_by_largest', 'Trier par plus grand'],
            ['en', 'selected_only', 'Selected Only'],
            ['fr', 'selected_only', 'Sélectionnés uniquement'],
            ['en', 'search_your_files', 'Search your files'],
            ['fr', 'search_your_files', 'Rechercher vos fichiers'],
            ['en', '0_file_selected', '0 File selected'],
            ['fr', '0_file_selected', '0 fichier sélectionné'],
            ['en', 'delete_selection', 'Delete Selection'],
            ['fr', 'delete_selection', 'Supprimer la sélection'],
            ['en', 'add_files', 'Add Files'],
            ['fr', 'add_files', 'Ajouter des fichiers'],
            ['en', 'delete_confirmation', 'Delete Confirmation'],
            ['fr', 'delete_confirmation', 'Confirmation de suppression'],
            ['en', 'are_you_sure_you_want_to_delete_this_file', 'Are you sure you want to delete this file?'],
            ['fr', 'are_you_sure_you_want_to_delete_this_file', 'Êtes-vous sûr de vouloir supprimer ce fichier ?'],

            // Marketplace & Storefront
            ['en', 'furniture__decoration_marketplace_in_morocco', 'Furniture & Decoration Marketplace in Morocco'],
            ['fr', 'furniture__decoration_marketplace_in_morocco', 'Marketplace de Mobilier & Décoration au Maroc'],
            ['en', 'explore_mayush_moroccan_marketplace_for_furniture_decoration_lighting_and_interior_design_with', 'Explore Mayush, Moroccan marketplace for furniture, decoration, lighting, and interior design with'],
            ['fr', 'explore_mayush_moroccan_marketplace_for_furniture_decoration_lighting_and_interior_design_with', 'Explorez Mayush, marketplace marocaine de mobilier, décoration, luminaires et aménagement intérieur avec'],
            ['en', 'published_products_and', 'published products and'],
            ['fr', 'published_products_and', 'produits publiés et'],
            ['en', 'verified_sellers', 'verified sellers'],
            ['fr', 'verified_sellers', 'vendeurs vérifiés'],
            ['en', 'mayush_seller_in_morocco', 'Mayush seller in Morocco'],
            ['fr', 'mayush_seller_in_morocco', 'vendeur Mayush au Maroc'],
            ['en', 'verified_mayush_seller', 'Verified Mayush Seller'],
            ['fr', 'verified_mayush_seller', 'Vendeur vérifié Mayush'],
            ['en', 'referenced_mayush_seller', 'Referenced Mayush Seller'],
            ['fr', 'referenced_mayush_seller', 'Vendeur référencé Mayush'],
            ['en', 'published_products', 'published products'],
            ['fr', 'published_products', 'produits publiés'],

            // Product & Attributes
            ['en', 'attribute_information', 'Attribute Information'],
            ['fr', 'attribute_information', 'Informations d\'attribut'],
            ['en', 'attribute_name', 'Attribute Name'],
            ['fr', 'attribute_name', 'Nom de l\'attribut'],
            ['en', 'attribute_value', 'Attribute Value'],
            ['fr', 'attribute_value', 'Valeur de l\'attribut'],
            ['en', 'enter_attribute_value', 'Enter Attribute Value'],
            ['fr', 'enter_attribute_value', 'Entrez la valeur de l\'attribut'],
            ['en', 'add_more', 'Add More'],
            ['fr', 'add_more', 'Ajouter plus'],
            ['en', 'search_by_product_namebarcode', 'Search by Product Name/Barcode'],
            ['fr', 'search_by_product_namebarcode', 'Rechercher par nom de produit / code-barres'],

            // Notes & Custom Labels
            ['en', 'all_notes', 'All Notes'],
            ['fr', 'all_notes', 'Toutes les notes'],
            ['en', 'add_new_note', 'Add New Note'],
            ['fr', 'add_new_note', 'Ajouter une nouvelle note'],
            ['en', 'seller_can_add_note', 'Seller Can Add Note'],
            ['fr', 'seller_can_add_note', 'Le vendeur peut ajouter une note'],
            ['en', 'type__enter', 'Type & Enter'],
            ['fr', 'type__enter', 'Tapez & Entrée'],
        ];

        foreach ($translations as $item) {
            DB::table('translations')->updateOrInsert(
                [
                    'lang' => $item[0],
                    'lang_key' => $item[1],
                ],
                [
                    'lang_value' => $item[2],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        Cache::forget('translations-en');
        Cache::forget('translations-fr');
    }
}

