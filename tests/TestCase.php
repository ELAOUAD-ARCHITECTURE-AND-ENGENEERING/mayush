<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'app_lang_code' => 'en', 'rtl' => 0]
        );

        $settings = [
            'site_name'           => 'MayushTest',
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
            \App\Models\BusinessSetting::updateOrCreate(['type' => $key], ['value' => $value]);
        }
    }

    protected function tearDown(): void
    {
        if (class_exists('Mockery')) {
            \Mockery::close();
        }
        parent::tearDown();
    }
}
