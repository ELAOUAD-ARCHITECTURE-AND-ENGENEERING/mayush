<?php

namespace Database\Seeders;

use App\Models\Inspiration;
use App\Models\InspirationHotspot;
use App\Models\InspirationItem;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InspirationsSeeder extends Seeder
{
    public function run(): void
    {
        // Available published products with thumbnail images
        $products = Product::where('published', 1)
            ->whereNotNull('thumbnail_img')
            ->where('thumbnail_img', '!=', '')
            ->orderBy('id')
            ->get();

        if ($products->count() < 24) {
            $products = Product::whereNotNull('thumbnail_img')->orderBy('id')->get();
        }

        if ($products->count() < 4) {
            throw new \RuntimeException('At least four catalog products with thumbnails are required to seed Inspirations.');
        }

        $pIndex = 0;
        $getProducts = function (int $count) use ($products, &$pIndex) {
            $selected = collect();
            for ($i = 0; $i < $count; $i++) {
                if ($products->isEmpty()) break;
                $selected->push($products[$pIndex % $products->count()]);
                $pIndex++;
            }
            return $selected;
        };

        $referenceArtDir = base_path('mayush-mobile/assets/reference-art');

        $inspirationsData = [
            [
                'slug' => 'esprit-japandi',
                'title_fr' => 'Esprit Japandi',
                'title_ar' => 'روح الجاباندي',
                'subtitle_fr' => 'Un salon chaleureux, minimaliste et naturel',
                'subtitle_ar' => 'صالون دافئ، بسيط وطبيعي',
                'description_fr' => 'Le style Japandi allie le minimalisme scandinave à l\'esthétique japonaise wabi-sabi. Privilégiez les lignes épurées, le bois clair et les teintes naturelles pour un intérieur apaisant.',
                'description_ar' => 'يجمع أسلوب جاباندي بين البساطة الاسكندنافية والجماليات اليابانية. اختر الخطوط النقية والخشب الفاتح والألوان الطبيعية لمنزل مريح.',
                'source_image' => 'home-inspiration-japandi.png',
                'target_image' => 'inspirations/esprit-japandi.png',
                'sort_order' => 1,
                'hotspots' => [
                    ['x' => 0.32, 'y' => 0.72],
                    ['x' => 0.70, 'y' => 0.76],
                    ['x' => 0.65, 'y' => 0.55],
                    ['x' => 0.77, 'y' => 0.62],
                ],
            ],
            [
                'slug' => 'ambiance-boheme-chic',
                'title_fr' => 'Ambiance Bohème Chic',
                'title_ar' => 'أجواء بوهيمية أنيقة',
                'subtitle_fr' => 'Chaleur, authenticité et artisanat',
                'subtitle_ar' => 'دفء وأصالة وصناعة تقليدية',
                'description_fr' => 'Mêlez les matières artisanales, les textiles tissés et les motifs chaleureux pour composer un intérieur vivant empreint de douceur.',
                'description_ar' => 'امزج بين المواد المصنوعة يدوياً والمنسوجات والألوان الدافئة لخلق مساحة حيوية مفعمة بالأناقة والراحة.',
                'source_image' => 'home-inspiration-natural.png',
                'target_image' => 'inspirations/ambiance-boheme-chic.png',
                'sort_order' => 2,
                'hotspots' => [
                    ['x' => 0.27, 'y' => 0.20],
                    ['x' => 0.16, 'y' => 0.55],
                    ['x' => 0.49, 'y' => 0.62],
                    ['x' => 0.88, 'y' => 0.55],
                ],
            ],
            [
                'slug' => 'salon-contemporain-epure',
                'title_fr' => 'Salon Contemporain Épuré',
                'title_ar' => 'صالون معاصر وأنيق',
                'subtitle_fr' => 'Élégance, matières nobles et modernité',
                'subtitle_ar' => 'أناقة، مواد راقية وعصرية',
                'description_fr' => 'Des volumes généreux, des assises sculpturales et des contrastes raffinés de marbre et de bois sombre créent une atmosphère luxueuse et accueillante.',
                'description_ar' => 'مساحات فسيحة ومقاعد بتصاميم مميزة وتناغم راقٍ بين الرخام والخشب الداكن يخلق أجواء مريحة وفاخرة.',
                'source_image' => 'home-hero-premium-scene.png',
                'target_image' => 'inspirations/salon-contemporain-epure.png',
                'sort_order' => 3,
                'hotspots' => [
                    ['x' => 0.35, 'y' => 0.64],
                    ['x' => 0.65, 'y' => 0.78],
                    ['x' => 0.57, 'y' => 0.68],
                    ['x' => 0.82, 'y' => 0.65],
                ],
            ],
            [
                'slug' => 'chambre-cocon-serenite',
                'title_fr' => 'Chambre Cocon & Sérénité',
                'title_ar' => 'غرفة نوم هادئة ومريحة',
                'subtitle_fr' => 'Douceur, repos et matières veloutées',
                'subtitle_ar' => 'نعومة، استرخاء وأقمشة مريحة',
                'description_fr' => 'Un espace dédié au calme absolu : lit habillé de lin doux, fauteuil d\'appoint enveloppant et lumières tamisées.',
                'description_ar' => 'مساحة مخصصة للهدوء التام: سرير ببياضات مريحة وكرسي وثيق وإضاءة دافئة ناعمة.',
                'source_image' => 'home-moodboard-chambre.png',
                'target_image' => 'inspirations/chambre-cocon-serenite.png',
                'sort_order' => 4,
                'hotspots' => [
                    ['x' => 0.27, 'y' => 0.68],
                    ['x' => 0.10, 'y' => 0.35],
                    ['x' => 0.55, 'y' => 0.58],
                    ['x' => 0.82, 'y' => 0.68],
                ],
            ],
            [
                'slug' => 'espace-bureau-creativite',
                'title_fr' => 'Espace Bureau & Créativité',
                'title_ar' => 'مكتب منزلي للإبداع',
                'subtitle_fr' => 'Design fonctionnel et concentration',
                'subtitle_ar' => 'تصميم عملي وتركيز',
                'description_fr' => 'Un environnement de travail clair et stimulant où l\'ergonomie rencontre l\'esthétique pour libérer toute votre inspiration.',
                'description_ar' => 'بيئة عمل واضحة ومحفزة تجمع بين الراحة والجمال لإطلاق كل إبداعاتك.',
                'source_image' => 'home-moodboard-bureau.png',
                'target_image' => 'inspirations/espace-bureau-creativite.png',
                'sort_order' => 5,
                'hotspots' => [
                    ['x' => 0.52, 'y' => 0.80],
                    ['x' => 0.58, 'y' => 0.68],
                    ['x' => 0.39, 'y' => 0.56],
                    ['x' => 0.74, 'y' => 0.48],
                ],
            ],
            [
                'slug' => 'terrasse-jardin-hiver',
                'title_fr' => 'Terrasse & Jardin d\'Hiver',
                'title_ar' => 'شرفة وحديقة شتوية',
                'subtitle_fr' => 'Détente en plein air et touches végétales',
                'subtitle_ar' => 'استرخاء في الهواء الطلق ولمسات نباتية',
                'description_fr' => 'Prolongez votre art de vivre vers l\'extérieur grâce à du mobilier résistant aux lignes chaleureuses entouré de verdure.',
                'description_ar' => 'استمتع بجلسات خارجية راقية بأثاث متين ولمسات دافئة محاطة بالنباتات الطبيعية.',
                'source_image' => 'home-hero-scene.png',
                'target_image' => 'inspirations/terrasse-jardin-hiver.png',
                'sort_order' => 6,
                'hotspots' => [
                    ['x' => 0.32, 'y' => 0.74],
                    ['x' => 0.59, 'y' => 0.78],
                    ['x' => 0.62, 'y' => 0.15],
                    ['x' => 0.85, 'y' => 0.78],
                ],
            ],
        ];

        $expiredScene = $inspirationsData[1];
        $expiredScene['slug'] = 'ambiance-boheme-expiree';
        $expiredScene['title_fr'] = 'Ambiance Bohème — archive temporelle';
        $expiredScene['title_ar'] = 'أجواء بوهيمية منتهية';
        $expiredScene['target_image'] = 'inspirations/ambiance-boheme-expiree.png';
        $expiredScene['sort_order'] = 7;
        $inspirationsData[] = $expiredScene;

        $publicationScenarios = [
            'esprit-japandi' => ['status' => 'published', 'featured' => true],
            'ambiance-boheme-chic' => ['status' => 'published', 'featured' => true],
            'salon-contemporain-epure' => ['status' => 'published', 'featured' => true],
            'chambre-cocon-serenite' => ['status' => 'draft', 'featured' => false],
            'espace-bureau-creativite' => ['status' => 'archived', 'featured' => false],
            'terrasse-jardin-hiver' => ['status' => 'published', 'featured' => false, 'starts_at' => now()->addDay()],
            'ambiance-boheme-expiree' => ['status' => 'published', 'featured' => false, 'ends_at' => now()->subDay()],
        ];

        foreach ($inspirationsData as $data) {
            $destRelPath = $data['target_image'];

            $sourceFullPath = $referenceArtDir.DIRECTORY_SEPARATOR.$data['source_image'];
            if (!File::isFile($sourceFullPath)) {
                // Generate a valid image fixture when asset is not present on disk
                $img = imagecreatetruecolor(1200, 800);
                $bg = imagecolorallocate($img, 235, 230, 224);
                imagefilledrectangle($img, 0, 0, 1200, 800, $bg);
                ob_start();
                imagepng($img);
                $imageData = ob_get_clean();
                imagedestroy($img);
                Storage::disk('public')->put($destRelPath, $imageData);
                $imgWidth = 1200;
                $imgHeight = 800;
            } else {
                $size = getimagesize($sourceFullPath);
                if ($size === false) {
                    throw new \RuntimeException("Invalid Inspiration source image: {$sourceFullPath}");
                }
                [$imgWidth, $imgHeight] = $size;
                Storage::disk('public')->put($destRelPath, File::get($sourceFullPath));
            }

            // Create or update Inspiration
            $inspiration = Inspiration::withTrashed()->where('slug', $data['slug'])->first();
            if (!$inspiration) {
                $inspiration = new Inspiration();
            } else {
                $inspiration->restore();
            }

            $inspiration->slug = $data['slug'];
            $inspiration->title_fr = $data['title_fr'];
            $inspiration->title_ar = $data['title_ar'];
            $inspiration->subtitle_fr = $data['subtitle_fr'];
            $inspiration->subtitle_ar = $data['subtitle_ar'];
            $inspiration->description_fr = $data['description_fr'];
            $inspiration->description_ar = $data['description_ar'];
            $inspiration->hero_image = $destRelPath;
            $inspiration->hero_image_width = $imgWidth;
            $inspiration->hero_image_height = $imgHeight;
            $scenario = $publicationScenarios[$data['slug']];
            $inspiration->status = $scenario['status'];
            $inspiration->is_featured = $scenario['featured'];
            $inspiration->show_on_home = $scenario['featured'];
            $inspiration->sort_order = $data['sort_order'];
            $inspiration->published_at = $scenario['status'] === 'published' ? now() : null;
            $inspiration->starts_at = $scenario['starts_at'] ?? null;
            $inspiration->ends_at = $scenario['ends_at'] ?? null;
            $inspiration->save();

            // Clear previous items and hotspots
            $inspiration->items()->delete();
            $inspiration->hotspots()->delete();

            // Pick 4 distinct products
            $assignedProducts = $getProducts(4);
            $order = 1;

            foreach ($assignedProducts as $idx => $prod) {
                $item = $inspiration->items()->create([
                    'product_id' => $prod->id,
                    'display_order' => $order,
                    'is_visible' => true,
                    'is_featured' => true,
                ]);

                $hsCoord = $data['hotspots'][$idx] ?? ['x' => 0.2 + ($idx * 0.2), 'y' => 0.4 + ($idx * 0.1)];

                $item->hotspot()->create([
                    'inspiration_id' => $inspiration->id,
                    'x' => $hsCoord['x'],
                    'y' => $hsCoord['y'],
                    'display_order' => $order,
                ]);

                $order++;
            }
        }

        app(\App\Services\InspirationCacheService::class)->invalidate();
    }
}
