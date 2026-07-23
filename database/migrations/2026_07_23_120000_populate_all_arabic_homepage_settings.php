<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
            'todays_deal_title' => 'القطع الأكثر طلباً',
            'todays_deal_subtitle' => 'عرض اليوم المحدود',
            'todays_deal_description' => 'استمتع بتخفيضات استثنائية على منتجاتنا المميزة. لا تفوت هذه العروض المحدودة.',
            'todays_deal_banner' => '3165',
            'todays_deal_banner_small' => '3166',

            'home_slider_titles' => json_encode(['<span style="color: rgb(255, 255, 255);">ماذا لو كان منزلكم</span> <span style="color: rgb(255, 156, 0);">يعبر عن شخصيتكم</span><span style="color: #ff9c00;">؟</span>'], JSON_UNESCAPED_UNICODE),
            'home_slider_descriptions' => json_encode(['أثاث وديكور عصري مصمم بعناية ليضفي جمالاً وطابعاً فريداً على مساحتكم. التوصيل لجميع المدن والدفع عند الاستلام.'], JSON_UNESCAPED_UNICODE),
            'home_slider_cta_texts' => json_encode(['اكتشفوا تشكيلاتنا'], JSON_UNESCAPED_UNICODE),
            'home_slider_images' => json_encode(['5818']),
            'home_slider_links' => json_encode([null]),
            'home_slider_cta_links' => json_encode(['/collections/selection-mayush']),

            'home_banner1_titles' => json_encode(['إضاءة تبرز جمال المساحات', 'خامات وتصاميم لمشاريع مميزة'], JSON_UNESCAPED_UNICODE),
            'home_banner1_descriptions' => json_encode(['ثريات ومصابيح مختارة بعناية لمنح غرفكم عمقاً وأناقة.', 'أحجار ولمسات نهائية تحول أفكاركم إلى مساحات راقية.'], JSON_UNESCAPED_UNICODE),
            'home_banner1_cta_texts' => json_encode([null, null]),
            'home_banner1_images' => json_encode(['5784', '5787']),
            'home_banner1_links' => json_encode([null, null]),
            'home_banner1_collection_ids' => json_encode(['1', '2']),

            'home_banner4_titles' => json_encode(['غيروا الأجواء دون تغيير الغرفة', 'حيث تُشارك أجمل اللحظات'], JSON_UNESCAPED_UNICODE),
            'home_banner4_descriptions' => json_encode(['إضاءة مختارة بعناية تحول المكان، تبرز التفاصيل وتخلق أجواءً دافئة.', 'من جلسات القهوة السريعة إلى الوجبات العائلية، اكتشفوا تشكيلة تجعل مطبخكم قلب المنزل.'], JSON_UNESCAPED_UNICODE),
            'home_banner4_cta_texts' => json_encode(['خلق الأجواء', 'تصفح المطبخ'], JSON_UNESCAPED_UNICODE),
            'home_banner4_images' => json_encode(['5784', '5787']),
            'home_banner4_links' => json_encode(['https://mayushdesign.com/category/eclairage', 'https://mayushdesign.com/category/decocuisine']),
            'home_banner4_collection_ids' => json_encode(['3', '4']),

            'metro_collections_newest_image' => '5877',
            'metro_collections_newest_cta_link' => '/collections/nouvelles-collections',
            'metro_collections_best_selling_image' => '5876',
            'metro_collections_best_selling_cta_link' => '/collections/les-plus-populaires',
        ];

        foreach ($settings as $type => $value) {
            DB::table('business_settings')->updateOrInsert(
                ['type' => $type, 'lang' => 'ma'],
                ['value' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        // 2. Translations table for Arabic (lang = 'ma')
        $translations = [
            'Days' => 'أيام',
            'Hours' => 'ساعات',
            'Minutes' => 'دقائق',
            'Seconds' => 'ثواني',
            'Inspiration & Conseils' => 'إلهام ونصائح',
            'Time remaining before today\'s deals reset' => 'الوقت المتبقي قبل إعادة ضبط عروض اليوم',
            'Promoted Ads' => 'إعلانات مروجة',
            'Products Available' => 'منتجات متاحة',
            'Limited Time Offer' => 'عرض لفترة محدودة',
            'Cyber Deals' => 'صفقات مميزة',
            'Category Navigation' => 'التنقل بين الفئات',
            'Featured Categories' => 'الفئات المميزة',
            'Featured Products' => 'المنتجات المميزة',
            'New Collections' => 'تشكيلات جديدة',
            'Best Selling' => 'الأكثر مبيعاً',
            'Top Sellers' => 'أفضل البائعين',
            'Shop Now' => 'تسوق الآن',
            'View All' => 'عرض الكل',
            'read more' => 'اقرأ المزيد',
            'Mayush furniture and decor marketplace promotion' => 'عروض مايوش للأثاث والديكور العصري',
        ];

        foreach ($translations as $key => $value) {
            $langKey = strtolower(preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', $key)));

            DB::table('translations')->updateOrInsert(
                ['lang' => 'ma', 'lang_key' => $langKey],
                ['lang_value' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        // 3. Custom Alert cookie popup translation update
        DB::table('custom_alerts')->where('id', 1)->update([
            'description' => '<p>نحن نستخدم ملفات تعريف الارتباط لتحسين تجربتك، تصفح سياسة الخصوصية <a href="/privacy-policy">هنا</a></p>',
            'updated_at' => $now,
        ]);

        // 4. Invalidate all business settings & storefront caches
        Cache::forget('business_settings');
        if (class_exists(\App\Services\StorefrontCacheService::class)) {
            app(\App\Services\StorefrontCacheService::class)->bump();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive down required for localization updates
    }
};
