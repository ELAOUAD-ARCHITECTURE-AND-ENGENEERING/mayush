<?php

namespace Tests\Traits;

use App\Models\BusinessSetting;
use App\Models\Language;

trait SeedsAppConfigs
{
    protected function seedConfigs(): void
    {
        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );

        foreach ($this->defaultBusinessSettings() as $type => $value) {
            BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
        }
    }

    protected function defaultBusinessSettings(): array
    {
        return [
            'site_name' => 'MayushTest',
            'website_name' => 'MayushTest',
            'site_motto' => 'Design Marketplace',
            'language' => 'en',
            'homepage_select' => 'classic',
            'authentication_layout_select' => 'boxed',
            'header_element' => null,
            'home_slider_images' => null,
            'home_banner1_images' => null,
            'home_banner2_images' => null,
            'home_banner3_images' => null,
            'top10_categories' => null,
            'top10_brands' => null,
            'classified_product' => '0',
            'portfolio_landing' => '0',
            'facebook_comment' => '0',
            'google_login' => '0',
            'facebook_login' => '0',
            'twitter_login' => '0',
            'apple_login' => '0',
            'google_recaptcha' => '0',
            'color_scheme' => 'default',
            'frontend_logo' => null,
            'meta_image' => null,
            'header_logo' => null,
        ];
    }
}
