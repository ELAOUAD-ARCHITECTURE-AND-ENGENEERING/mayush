<?php

namespace Tests\Traits;

use App\Models\Language;
use App\Models\BusinessSetting;

trait SeedsAppConfigs
{
    /**
     * Seed essential configurations required for view rendering.
     */
    protected function seedConfigs(): void
    {
        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );

        $settings = [
            'site_name'           => 'MayushTest',
            'website_name'        => 'MayushTest',
            'site_motto'          => 'Design Marketplace',
            'language'            => 'en',
            'home_slider_images'  => null,
            'home_banner1_images' => null,
            'home_banner2_images' => null,
            'home_banner3_images' => null,
            'top10_categories'    => null,
            'top10_brands'        => null,
            'classified_product'  => '0',
            'google_login'        => '0',
            'facebook_login'      => '0',
            'twitter_login'       => '0',
            'apple_login'         => '0',
            'google_recaptcha'    => '0',
            'color_scheme'        => 'default',
            'frontend_logo'       => null,
        ];

        foreach ($settings as $key => $value) {
            BusinessSetting::updateOrCreate(['type' => $key], ['value' => $value]);
        }
    }
}
