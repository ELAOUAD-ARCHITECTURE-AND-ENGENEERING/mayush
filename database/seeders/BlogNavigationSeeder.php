<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class BlogNavigationSeeder extends Seeder
{
    public function run(): void
    {
        $labels = $this->decodeSetting('header_menu_labels');
        $links = $this->decodeSetting('header_menu_links');

        $blogIndex = collect($labels)->search(function ($label) {
            return strtolower(trim((string) $label)) === 'blog';
        });

        if ($blogIndex === false) {
            $labels[] = 'Blog';
            $links[] = '/blog';
        } else {
            $links[$blogIndex] = '/blog';
        }

        BusinessSetting::updateOrCreate(
            ['type' => 'header_menu_labels'],
            ['value' => json_encode(array_values($labels))]
        );

        BusinessSetting::updateOrCreate(
            ['type' => 'header_menu_links'],
            ['value' => json_encode(array_values($links))]
        );

        Cache::forget('business_settings');
    }

    private function decodeSetting(string $type): array
    {
        $value = BusinessSetting::where('type', $type)->value('value');
        $decoded = json_decode($value ?: '[]', true);

        return is_array($decoded) ? $decoded : [];
    }
}
