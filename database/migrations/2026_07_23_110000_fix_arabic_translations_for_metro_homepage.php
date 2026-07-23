<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        // 1. Business settings for Arabic (lang = 'ma')
        $settings = [
            'todays_deal_title' => "القطع الأكثر طلباً",
            'todays_deal_subtitle' => "عرض اليوم المحدود",
            'todays_deal_description' => "استمتع بتخفيضات استثنائية على منتجاتنا المميزة. لا تفوت هذه العروض المحدودة.",
        ];

        foreach ($settings as $type => $value) {
            DB::table('business_settings')->updateOrInsert(
                ['type' => $type, 'lang' => 'ma'],
                ['value' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        // 2. Translations for Arabic (lang = 'ma')
        $translations = [
            'Days' => 'أيام',
            'Hours' => 'ساعات',
            'Minutes' => 'دقائق',
            'Seconds' => 'ثواني',
            'Inspiration & Conseils' => 'إلهام ونصائح',
            'Time remaining before today\'s deals reset' => 'الوقت المتبقي قبل إعادة ضبط عروض اليوم',
        ];

        foreach ($translations as $key => $value) {
            $langKey = strtolower(preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', $key)));

            DB::table('translations')->updateOrInsert(
                ['lang' => 'ma', 'lang_key' => $langKey],
                ['lang_value' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destruct behavior needed for localization updates
    }
};
