<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTranslation;
use App\Models\Tag;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::firstOrCreate(
            ['email' => 'editor@mayush.test'],
            [
                'name' => 'Mayush Editorial',
                'password' => bcrypt(Str::random(32)),
                'user_type' => 'admin',
                'email_verified_at' => now(),
                'verification_status' => 1,
            ]
        );

        $category = BlogCategory::firstOrCreate(
            ['slug' => 'interior-design-guides'],
            ['category_name' => 'Interior Design Guides', 'status' => 1]
        );

        foreach ($this->articles() as $index => $article) {
            $upload = Upload::firstOrCreate(
                ['file_original_name' => $article['slug']],
                [
                    'file_name' => 'assets/img/placeholder-rect.jpg',
                    'user_id' => $author->id,
                    'extension' => 'jpg',
                    'type' => 'image',
                    'file_size' => 0,
                ]
            );

            $blog = Blog::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'category_id' => $category->id,
                    'user_id' => $author->id,
                    'title' => $article['translations']['en']['title'],
                    'banner' => $upload->id,
                    'short_description' => $article['translations']['en']['short_description'],
                    'description' => $article['translations']['en']['description'],
                    'meta_title' => $article['translations']['en']['title'],
                    'meta_img' => $upload->id,
                    'meta_description' => $article['translations']['en']['short_description'],
                    'meta_keywords' => implode(', ', $article['tags']),
                    'status' => 1,
                    'published_at' => now()->subDays(count($this->articles()) - $index),
                ]
            );

            $tagIds = collect($article['tags'])->map(function (string $name) {
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name]
                )->id;
            });
            $blog->tags()->sync($tagIds);

            foreach ($article['translations'] as $lang => $translation) {
                BlogTranslation::updateOrCreate(
                    ['blog_id' => $blog->id, 'lang' => $lang],
                    [
                        'title' => $translation['title'],
                        'short_description' => $translation['short_description'],
                        'description' => $translation['description'],
                        'meta_title' => $translation['title'],
                        'meta_description' => $translation['short_description'],
                        'meta_keywords' => implode(', ', $article['tags']),
                    ]
                );
            }
        }
    }

    private function articles(): array
    {
        return [
            $this->article('sustainable-living-decor', 'Sustainable Living Decor', 'Choose durable materials, efficient lighting, and handmade accents for a warmer low-waste home.', ['sustainable decor', 'materials']),
            $this->article('color-trends-2026', 'Color Trends 2026', 'Use grounded neutrals, mineral greens, and confident accent colors to refresh Moroccan interiors.', ['color trends', 'paint']),
            $this->article('space-saving-solutions', 'Space Saving Solutions', 'Plan modular storage, slimmer furniture, and flexible rooms without sacrificing comfort.', ['small spaces', 'storage']),
            $this->article('traditional-zellij-tiles', 'Traditional Zellij Tiles', 'Bring artisanal texture into kitchens, bathrooms, and entryways with practical zellij choices.', ['zellij', 'craft']),
            $this->article('lighting-magic', 'Lighting Magic', 'Layer ambient, task, and accent lighting to make every room easier to use and more inviting.', ['lighting', 'ambience']),
            $this->article('minimalist-decor', 'Minimalist Decor', 'Create calm rooms with fewer objects, better proportions, and natural textures.', ['minimalism', 'decor']),
            $this->article('rooftop-garden-oasis', 'Rooftop Garden Oasis', 'Turn rooftops into shaded outdoor rooms with planters, seating, and weather-ready materials.', ['garden', 'outdoor']),
            $this->article('perfect-sofa-guide', 'Perfect Sofa Guide', 'Compare scale, fabric, frame quality, and maintenance before choosing a sofa.', ['sofa', 'buying guide']),
            $this->article('home-office-setup', 'Home Office Setup', 'Build a focused work area with ergonomic furniture, cable control, and balanced light.', ['home office', 'ergonomics']),
            $this->article('kitchen-renovations-2026', 'Kitchen Renovations 2026', 'Prioritize workflow, surfaces, storage, and lighting for a kitchen that lasts.', ['kitchen', 'renovation']),
        ];
    }

    private function article(string $slug, string $title, string $summary, array $tags): array
    {
        return [
            'slug' => $slug,
            'tags' => $tags,
            'translations' => [
                'en' => [
                    'title' => $title,
                    'short_description' => $summary,
                    'description' => '<p>'.$summary.'</p><p>Mayush recommends starting with the room purpose, measuring carefully, and choosing pieces that balance beauty with daily use.</p>',
                ],
                'fr' => [
                    'title' => $title,
                    'short_description' => $summary,
                    'description' => '<p>'.$summary.'</p><p>Mayush recommande de commencer par l usage de la piece, de mesurer avec soin et de choisir des elements beaux et pratiques.</p>',
                ],
                'ar' => [
                    'title' => $title,
                    'short_description' => $summary,
                    'description' => '<p>'.$summary.'</p><p>توصي Mayush بالبدء من وظيفة الغرفة، ثم القياس بدقة، واختيار قطع تجمع بين الجمال والاستخدام اليومي.</p>',
                ],
            ],
        ];
    }
}
