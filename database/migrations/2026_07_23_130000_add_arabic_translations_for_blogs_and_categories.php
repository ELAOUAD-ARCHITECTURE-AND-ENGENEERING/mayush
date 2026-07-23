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

        $translations = [
            // Blog Categories
            'Interior Design Guides' => 'أدلة التصميم الداخلي',
            'Interior Design Ideas' => 'أفكار التصميم الداخلي',
            'Architecture Inspiration' => 'إلهام المعمار',
            'Home Renovation' => 'تجديد المنازل',
            'Construction Guides' => 'أدلة البناء',
            '3D Visualization' => 'التصميم ثلاثي الأبعاد',
            'Furniture Buying Guides' => 'أدلة شراء الأثاث',
            'Lighting Design' => 'تصميم الإضاءة',
            'Kitchen Design' => 'تصميم المطبخ',
            'Bathroom Design' => 'تصميم الحمام',
            'Living Room Design' => 'تصميم الصالون',
            'Bedroom Design' => 'تصميم غرفة النوم',
            'Office and Workspace Design' => 'تصميم المكتب ومساحة العمل',
            'Outdoor and Garden Design' => 'تصميم الحدائق والمساحات الخارجية',
            'Small Space Solutions' => 'حلول المساحات الصغيرة',
            'Luxury Interiors' => 'التصميم الداخلي الفاخر',
            'Minimalist Design' => 'التصميم البسيط',
            'Moroccan Design and Craft' => 'التصميم والصناعة التقليدية المغربية',
            'Materials and Finishes' => 'المواد واللمسات النهائية',
            'Decor and Accessories' => 'الديكور والإكسسوارات',

            // Blog Titles
            'Designing the Perfect Home Office: Maximizing Productivity, Comfort, and Ergonomics' => 'تصميم المكتب المنزلي المثالي: تعظيم الإنتاجية والراحة وبيئة العمل',
            '7 Small Living Room Interior Design Ideas That Make Apartments Feel Bigger' => '7 أفكار لتصميم صالون صغير تجعل الشقق تبدو أكثر اتساعاً',
            'Modern Moroccan Interior Design: A Practical Room-by-Room Guide' => 'التصميم الداخلي المغربي العصري: دليل عملي لكل غرفة',
            'Bedroom Lighting Ideas: A Layered Interior Design Plan for Better Sleep' => 'أفكار إضاءة غرفة النوم: خطة إضاءة متعددة الطبقات لنوم أفضل',
            'Biophilic Interior Design: Bringing Nature Into Modern Urban Homes' => 'التصميم الداخلي البيوفيلي: إدخال الطبيعة إلى المنازل العصرية',
            'The Psychology of Color in Interior Design: How to Design Rooms That Enhance Well-being' => 'علم نفس الألوان في التصميم الداخلي: كيف تصمم غرفاً تعزز الراحة النفسية',
            'Mid-Century Modern vs. Japandi: The Ultimate Contrast and Blend of Minimalist Styles' => 'نمط منتصف القرن العصري مقابل جاباندي: التوازن المثالي للبساطة',

            // Blog Summaries
            'Build a highly productive, ergonomically correct, and visually beautiful home workspace that supports focus, prevents fatigue, and matches your home aesthetic.' => 'أنشئ مساحة عمل منزلية عالية الإنتاجية ومريحة جسدياً وأنيقة بصرياً تدعم التركيز وتمنع الإجهاد.',
            'Use these small living room interior design ideas to improve layout, storage, light, and furniture choices without making your apartment feel crowded.' => 'استخدم هذه الأفكار لتصميم الصالون الصغير لتحسين التوزيع والتخزين والإضاءة دون ازدحام.',
            'Create modern Moroccan interior design with zellij, carved wood, wool textiles, brass lighting, and calm contemporary room planning.' => 'ابتكر تصميماً مغربياً عصرياً باستخدام الزليج، الخشب المنقوش، والمنسوجات والإضاءة النحاسية.',
            'Plan bedroom lighting ideas with ambient, task, and accent layers so your room feels beautiful at night and supports better sleep.' => 'خطط لإضاءة غرفة النوم بجميع طبقاتها لتبدو غرفتك ساحرة ليلاً وتوفر نيوماً مريحاً.',
            'Create a serene, healthy, and nature-connected living space in any urban apartment using natural materials, plants, natural light, and organic textures.' => 'أنشئ مساحة معيشة هادئة وصحية مرتبطة بالطبيعة باستخدام النباتات والمواد الطبيعية والإضاءة.',
            'Understand how paint colors and accents affect mood, behavior, and cognitive function, and design room color schemes that promote calm, energy, and focus.' => 'افهم كيف تؤثر الألوان على المزاج والسلوك، وصمم تناسق ألوان يعزز الهدوء والتركيز.',
            'Explore the clean lines, organic curves, and elegant minimalism of Mid-Century Modern and Japandi interior design styles, and learn how to blend them seamlessly.' => 'استكشف الخطوط النظيفة واللمسات الأنيقة لأسلوبي منتصف القرن وجاباندي وتعلّم كيفية الدمج بينهما.',

            // Blog Card Footer Labels
            'min read' => 'دقيقة قراءة',
            'products' => 'منتجات',
            'Read guide' => 'اقرأ الدليل',
        ];

        foreach ($translations as $key => $value) {
            $langKey = strtolower(preg_replace('/[^A-Za-z0-9\_]/', '', str_replace(' ', '_', $key)));

            DB::table('translations')->updateOrInsert(
                ['lang' => 'ma', 'lang_key' => $langKey],
                ['lang_value' => $value, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        Cache::forget('translations-ma');
        Cache::forget('translations-en');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive down required
    }
};
