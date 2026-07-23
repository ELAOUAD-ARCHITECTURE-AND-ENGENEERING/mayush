<?php

namespace Tests\Traits;

use App\Models\BusinessSetting;
use App\Models\Currency;
use App\Models\Language;

trait SeedsAppConfigs
{
    protected function seedConfigs(): void
    {
        \App\Models\User::firstOrCreate(
            ['user_type' => 'admin'],
            [
                'name' => 'Admin User',
                'email' => 'admin@mayush.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        Language::updateOrCreate(
            ['code' => 'fr'],
            ['name' => 'French', 'app_lang_code' => 'fr', 'rtl' => 0, 'status' => 1]
        );
        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0, 'status' => 1]
        );
        Language::updateOrCreate(
            ['code' => 'ma'],
            ['name' => 'Arabic', 'app_lang_code' => 'ar', 'rtl' => 1, 'status' => 1]
        );

        foreach ($this->defaultBusinessSettings() as $type => $value) {
            BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
        }

        // Ensure a default currency exists so price formatting helpers don't crash
        Currency::unguard();
        Currency::updateOrCreate(
            ['code' => 'MAD'],
            [
                'name'          => 'Moroccan Dirham',
                'symbol'        => 'MAD',
                'exchange_rate'  => 1,
                'status'        => 1,
            ]
        );
        Currency::reguard();

        // Point system_default_currency to it
        $currency = Currency::where('code', 'MAD')->first();
        BusinessSetting::updateOrCreate(
            ['type' => 'system_default_currency'],
            ['value' => $currency->id]
        );
    }

    protected function defaultBusinessSettings(): array
    {
        return [
            'site_name' => 'MayushTest',
            'website_name' => 'MayushTest',
            'site_motto' => 'Design Marketplace',
            'language' => 'fr',
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
            // Search & product helpers
            'vendor_system_activation' => '1',
            'no_of_decimals' => '2',
            'symbol_format' => '3',
            'decimal_separator' => '1',
            'product_approve_by_admin' => '0',
        ];
    }
}
