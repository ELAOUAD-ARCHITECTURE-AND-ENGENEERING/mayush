<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BusinessSetting;
use App\Models\Translation;
use Cache;

class UpdateSeoStrategy extends Command
{
    protected $signature = 'seo:pivot-strategy';
    protected $description = 'Pivot SEO strategy to Luxury Interior Design and Smart Home Architecture';

    public function handle()
    {
        $this->info('Starting SEO Pivot Strategy...');

        $settings = [
            'meta_title' => 'Mayush | Luxury Interior Design Marketplace & Premium Home Decor',
            'meta_description' => 'Discover Mayush, the ultimate marketplace for luxury interior design products, premium furniture, and high-end home decor in Morocco. See before make it with our curated selection of designer materials.',
            'meta_keywords' => 'Luxury Furniture Marketplace, Premium Interior Decor, Designer Home Products, High-end Materials, Luxury Furnishings Morocco, Interior Design Shop, Modern Home Decor, Architectural Products',
            'site_motto' => 'See Before Make It - Luxury Interior Design Marketplace',
        ];

        foreach ($settings as $type => $value) {
            BusinessSetting::updateOrCreate(
                ['type' => $type, 'lang' => null],
                ['value' => $value]
            );
        }

        $this->info('Updating Translations...');

        $translations = [
            'en' => [
                'Turnkey Renovation Casablanca' => 'Luxury Interior Design Marketplace',
                'Renovation Excellence' => 'Product Excellence',
                'Your Renovation Partner' => 'Your Luxury Product Destination',
                'Top Moroccan Designer' => 'The #1 Luxury Marketplace in Morocco',
                'See before make it' => 'See before make it',
            ],
            'fr' => [
                'Turnkey Renovation Casablanca' => 'Marketplace de Design d\'Intérieur de Luxe',
                'Renovation Excellence' => 'Excellence des Produits',
                'Your Renovation Partner' => 'Votre Destination de Produits de Luxe',
                'Top Moroccan Designer' => 'La 1ère Marketplace de Luxe au Maroc',
                'See before make it' => 'Voir avant de réaliser',
            ],
            'ma' => [
                'Turnkey Renovation Casablanca' => 'متجر التصميم الداخلي الفاخر',
                'Renovation Excellence' => 'تميز المنتجات',
                'Your Renovation Partner' => 'وجهتك للمنتجات الفاخرة',
                'Top Moroccan Designer' => 'المتجر الأول للفخامة في المغرب',
                'See before make it' => 'شاهد قبل التنفيذ',
            ]
        ];

        foreach ($translations as $lang => $items) {
            foreach ($items as $key => $value) {
                Translation::updateOrCreate(
                    ['lang' => $lang, 'lang_key' => $key],
                    ['lang_value' => $value]
                );
            }
        }

        Cache::forget('business_settings');
        Cache::flush();

        $this->info('SEO Pivot Strategy completed successfully!');
    }
}
