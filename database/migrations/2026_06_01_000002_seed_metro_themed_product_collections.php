<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $definitions = [
            'lighting' => [
                'name' => 'Éclairage sculptural',
                'slug' => 'eclairage-sculptural',
                'description' => 'Suspensions, lampes et objets choisis pour donner du relief à vos pièces.',
                'category_ids' => [1],
                'default_sort' => 'popular',
                'hero_image' => 5078,
            ],
            'materials' => [
                'name' => 'Matières et textures',
                'slug' => 'matieres-et-textures',
                'description' => 'Pierres, textures et finitions pour composer des intérieurs singuliers.',
                'category_ids' => [5, 4, 223],
                'default_sort' => 'newest',
                'hero_image' => 5079,
            ],
            'autumn' => [
                'name' => 'Collection Automne',
                'slug' => 'collection-automne',
                'description' => 'Une sélection chaleureuse de mobilier, décoration et textiles pour transformer votre intérieur.',
                'category_ids' => [2, 224, 4, 223],
                'default_sort' => 'popular',
                'hero_image' => 5074,
            ],
            'salon' => [
                'name' => 'Pièces uniques pour le salon',
                'slug' => 'pieces-uniques-salon',
                'description' => 'Mobilier, tapis et objets décoratifs choisis pour créer un salon accueillant.',
                'category_ids' => [52, 224, 4],
                'default_sort' => 'popular',
                'hero_image' => 5075,
            ],
            'newest' => [
                'name' => 'Nouvelles collections',
                'slug' => 'nouvelles-collections',
                'description' => 'Les dernières pièces ajoutées au catalogue Mayush.',
                'category_ids' => [],
                'default_sort' => 'newest',
                'hero_image' => null,
            ],
            'popular' => [
                'name' => 'Les plus populaires',
                'slug' => 'les-plus-populaires',
                'description' => 'Les pièces les plus appréciées de notre catalogue.',
                'category_ids' => [],
                'default_sort' => 'popular',
                'hero_image' => null,
            ],
            'selection' => [
                'name' => 'Sélection Mayush',
                'slug' => 'selection-mayush',
                'description' => 'Mobilier, décoration, éclairage et textiles pour imaginer un intérieur qui vous ressemble.',
                'category_ids' => [2, 224, 1, 4, 6, 223],
                'default_sort' => 'popular',
                'hero_image' => 5080,
            ],
        ];

        DB::transaction(function () use ($definitions, $now) {
            $collections = [];

            foreach ($definitions as $key => $definition) {
                DB::table('product_collections')->updateOrInsert(
                    ['slug' => $definition['slug']],
                    [
                        'name' => $definition['name'],
                        'description' => $definition['description'],
                        'mode' => 'dynamic',
                        'category_ids' => json_encode($definition['category_ids']),
                        'brand_ids' => null,
                        'seller_ids' => null,
                        'tags' => null,
                        'min_price' => null,
                        'max_price' => null,
                        'default_sort' => $definition['default_sort'],
                        'hero_image' => $definition['hero_image'],
                        'meta_title' => null,
                        'meta_description' => null,
                        'meta_image' => null,
                        'show_best_selling' => true,
                        'show_recently_viewed' => true,
                        'status' => true,
                        'starts_at' => null,
                        'ends_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $collections[$key] = DB::table('product_collections')
                    ->where('slug', $definition['slug'])
                    ->value('id');
            }

            $this->saveLocalizedSetting('home_banner1_collection_ids', json_encode([
                $collections['lighting'],
                $collections['materials'],
            ]), $now);
            $this->saveLocalizedSetting('home_banner4_collection_ids', json_encode([
                $collections['autumn'],
                $collections['salon'],
            ]), $now);
            $this->saveLocalizedSetting('metro_collections_newest_cta_link', '/collections/nouvelles-collections', $now);
            $this->saveLocalizedSetting('metro_collections_best_selling_cta_link', '/collections/les-plus-populaires', $now);

            foreach (DB::table('business_settings')->where('type', 'home_slider_cta_links')->get() as $setting) {
                $links = json_decode($setting->value, true) ?: [];
                $links[0] = '/collections/selection-mayush';
                ksort($links);

                DB::table('business_settings')->where('id', $setting->id)->update([
                    'value' => json_encode(array_values($links)),
                    'updated_at' => $now,
                ]);
            }
        });

        Cache::forget('business_settings');
    }

    public function down(): void
    {
        $slugs = [
            'eclairage-sculptural',
            'matieres-et-textures',
            'collection-automne',
            'pieces-uniques-salon',
            'nouvelles-collections',
            'les-plus-populaires',
            'selection-mayush',
        ];

        DB::transaction(function () use ($slugs) {
            $collectionIds = DB::table('product_collections')->whereIn('slug', $slugs)->pluck('id');

            DB::table('product_collection_product')->whereIn('product_collection_id', $collectionIds)->delete();
            DB::table('product_collections')->whereIn('id', $collectionIds)->delete();
            DB::table('business_settings')->whereIn('type', [
                'home_banner1_collection_ids',
                'home_banner4_collection_ids',
                'metro_collections_newest_cta_link',
                'metro_collections_best_selling_cta_link',
            ])->delete();

            foreach (DB::table('business_settings')->where('type', 'home_slider_cta_links')->get() as $setting) {
                $links = json_decode($setting->value, true) ?: [];

                if (($links[0] ?? null) === '/collections/selection-mayush') {
                    $links[0] = null;
                    DB::table('business_settings')->where('id', $setting->id)->update([
                        'value' => json_encode($links),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        Cache::forget('business_settings');
    }

    private function saveLocalizedSetting(string $type, string $value, $now): void
    {
        foreach ([null, 'en', 'fr'] as $lang) {
            $query = DB::table('business_settings')->where('type', $type);
            $lang === null ? $query->whereNull('lang') : $query->where('lang', $lang);

            if ($query->exists()) {
                $query->update(['value' => $value, 'updated_at' => $now]);
            } else {
                DB::table('business_settings')->insert([
                    'type' => $type,
                    'value' => $value,
                    'lang' => $lang,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
