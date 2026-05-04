<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTranslation;
use App\Models\Tag;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeoInteriorDesignArticleSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::firstOrCreate(
            ['email' => 'editor@mayushdesign.com'],
            [
                'name' => 'Mayush Design Editorial Team',
                'password' => bcrypt(Str::random(32)),
                'user_type' => 'admin',
                'email_verified_at' => now(),
                'verification_status' => 1,
            ]
        );

        $category = $this->category();

        foreach ($this->articles() as $index => $article) {
            $imagePath = public_path($article['image']);
            $upload = Upload::updateOrCreate(
                ['file_name' => $article['image']],
                [
                    'file_original_name' => $article['slug'],
                    'user_id' => $author->id,
                    'extension' => pathinfo($article['image'], PATHINFO_EXTENSION),
                    'type' => 'image',
                    'file_size' => is_file($imagePath) ? filesize($imagePath) : 0,
                ]
            );

            $blog = Blog::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'category_id' => $category->id,
                    'user_id' => $author->id,
                    'title' => $article['title'],
                    'banner' => $upload->id,
                    'short_description' => $article['short_description'],
                    'description' => $article['description'],
                    'meta_title' => $article['meta_title'],
                    'meta_img' => $upload->id,
                    'meta_description' => $article['meta_description'],
                    'meta_keywords' => implode(', ', $article['keywords']),
                    'status' => 1,
                    'published_at' => now()->subHours(3 - $index),
                ]
            );

            $tagIds = collect($article['keywords'])->map(function (string $keyword) {
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($keyword)],
                    ['name' => Str::headline($keyword)]
                )->id;
            });

            $blog->tags()->sync($tagIds);

            BlogTranslation::updateOrCreate(
                ['blog_id' => $blog->id, 'lang' => 'en'],
                [
                    'title' => $article['title'],
                    'short_description' => $article['short_description'],
                    'description' => $article['description'],
                    'meta_title' => $article['meta_title'],
                    'meta_description' => $article['meta_description'],
                    'meta_keywords' => implode(', ', $article['keywords']),
                ]
            );
        }
    }

    private function category(): BlogCategory
    {
        $values = ['category_name' => 'Interior Design Guides'];

        if (Schema::hasColumn('blog_categories', 'status')) {
            $values['status'] = 1;
        }

        return BlogCategory::firstOrCreate(['slug' => 'interior-design-guides'], $values);
    }

    private function articles(): array
    {
        return [
            [
                'title' => '7 Small Living Room Interior Design Ideas That Make Apartments Feel Bigger',
                'slug' => 'small-living-room-interior-design',
                'image' => 'blog-assets/small-living-room-layout-infographic.svg',
                'keywords' => [
                    'small living room interior design',
                    'apartment living room ideas',
                    'space saving furniture',
                    'small room layout',
                    'interior design ideas',
                ],
                'short_description' => 'Use these small living room interior design ideas to improve layout, storage, light, and furniture choices without making your apartment feel crowded.',
                'meta_title' => '7 Small Living Room Interior Design Ideas',
                'meta_description' => 'Make a small living room feel bigger with layout rules, storage ideas, lighting tips, and a practical buying checklist for apartments.',
                'description' => <<<'HTML'
<p><strong>Small living room interior design</strong> is not about buying tiny furniture and hoping the room behaves. It is about controlling movement, scale, storage, light, and visual noise so the space feels calm instead of crowded.</p>
<p>If your living room also works as a TV area, guest corner, family room, and sometimes an office, the problem is not your taste. The problem is usually that every item is trying to do one job, while the room needs each item to do two.</p>
<p>This guide uses a practical apartment-first approach: measure the room, protect the walking path, choose pieces with visible legs, and use vertical storage so the floor can breathe.</p>

<h2>Table of Contents</h2>
<ul>
    <li><a href="#keyword-intent">Keyword and search intent</a></li>
    <li><a href="#layout-rules">Small living room layout rules</a></li>
    <li><a href="#furniture">Furniture that makes a room feel larger</a></li>
    <li><a href="#lighting-storage">Lighting and storage ideas</a></li>
    <li><a href="#buying-checklist">Buying checklist</a></li>
    <li><a href="#faq">FAQ</a></li>
</ul>

<h2 id="keyword-intent">Small Living Room Interior Design: Keyword and Intent</h2>
<p><strong>Primary keyword:</strong> small living room interior design.</p>
<p><strong>Semantic variants:</strong> apartment living room ideas, small room layout, space saving furniture, compact living room decor, small space storage.</p>
<p>The search intent is mostly educational with light buying intent. Readers want a room that feels bigger, but they also need to know what to buy, what to avoid, and how to arrange what they already own.</p>

<h2 id="layout-rules">Start With the 3-Zone Layout Rule</h2>
<p>Before choosing colors or accessories, divide the room into three zones: sitting, storage, and circulation. In a small apartment living room, circulation is the zone people forget. That is why beautiful rooms still feel annoying to use.</p>
<h3>Protect one clean walking line</h3>
<p>Keep one path from the entrance to the main seat, window, or balcony as open as possible. A clear walking line makes the room feel intentional, even when the room is modest.</p>
<p>For a typical 2.8m by 3.6m living room, Mayush recommends keeping the largest furniture against one long side and using a lightweight coffee table or nesting tables in the center. This leaves the eye with a clean floor shape.</p>
<h3>Float one piece if the room allows it</h3>
<p>Many people push every item against the wall. That can work, but a single floated chair, pouf, or table can make the room feel designed rather than stored. The trick is to float only one piece and keep it visually light.</p>

<h2 id="furniture">Choose Furniture That Shows More Floor</h2>
<p>The fastest way to make a small living room feel bigger is to reveal more floor. Pieces with slim legs, open bases, glass tops, or low arms create more visible space around them.</p>
<table>
    <thead>
        <tr>
            <th>Item</th>
            <th>Choose This</th>
            <th>Avoid This</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Sofa</td>
            <td>Compact sofa with raised legs and narrow arms</td>
            <td>Deep bulky sofa with oversized rolled arms</td>
        </tr>
        <tr>
            <td>Coffee table</td>
            <td>Nesting tables, round table, or storage ottoman</td>
            <td>Heavy rectangular table blocking movement</td>
        </tr>
        <tr>
            <td>Storage</td>
            <td>Tall closed cabinet and wall shelves</td>
            <td>Many small open baskets across the floor</td>
        </tr>
        <tr>
            <td>Accent seating</td>
            <td>Pouf, armless chair, or slim bench</td>
            <td>Second full-size sofa unless the room is wide</td>
        </tr>
    </tbody>
</table>

<h2 id="lighting-storage">Use Light and Storage to Reduce Visual Weight</h2>
<p>Lighting changes how big a room feels because it controls corners. Dark corners make walls feel closer. Lit corners make the room feel wider.</p>
<p>Use one ceiling light for general brightness, one floor or table lamp near the sofa, and one accent light on a shelf or wall. ENERGY STAR explains that well-designed LEDs are efficient and versatile, which makes them useful for layered home lighting: <a href="https://www.energystar.gov/products/learn-about-led-lighting" target="_blank" rel="noopener">learn about LED lighting</a>.</p>
<h3>Store vertically, display selectively</h3>
<p>Open shelving can look beautiful, but too much open storage makes a small room visually loud. Use closed storage for daily clutter and reserve open shelves for a few ceramics, books, frames, or handmade pieces.</p>
<p>If you like Moroccan textures, pair one handcrafted object with one calm surface. For example, a carved tray on a plain table reads as design. Ten small objects on the same table read as clutter.</p>

<h2 id="buying-checklist">Small Living Room Buying Checklist</h2>
<ul>
    <li>Measure the wall, doorway, and lift or stair access before buying the sofa.</li>
    <li>Choose one main storage piece instead of five small storage pieces.</li>
    <li>Keep the color palette to three dominant tones: wall, main upholstery, accent.</li>
    <li>Use mirrors opposite light or a nice view, not opposite clutter.</li>
    <li>Choose washable fabrics if the room is used daily by children or pets.</li>
</ul>

<h2>Internal Links for More Interior Design Planning</h2>
<p>For a warmer cultural look, read our guide to <a href="modern-moroccan-interior-design">modern Moroccan interior design</a>. If the room also needs a calmer evening mood, pair these ideas with our <a href="bedroom-lighting-ideas">bedroom lighting ideas</a> and apply the same layered-light principle to the living room.</p>

<h2 id="faq">FAQ</h2>
<h3>What color makes a small living room look bigger?</h3>
<p>Warm white, soft greige, pale clay, and muted green can all work. The best color is the one that reflects light while matching your sofa, rug, and floor. A bright white room with mismatched furniture can still feel busy.</p>
<h3>Should a small living room have a rug?</h3>
<p>Yes, if the rug is large enough. A rug that fits at least under the front sofa legs makes the seating zone feel complete. A tiny rug floating in the center can make the room feel smaller.</p>
<h3>What is the biggest small living room mistake?</h3>
<p>The biggest mistake is buying furniture one piece at a time without a layout. A modest sofa, table, and storage unit can still fail if they block the walking path.</p>

<h2>Author Note</h2>
<p><strong>Written by the Mayush Design Editorial Team.</strong> Our team studies real shopping behavior, Moroccan home layouts, and practical furnishing constraints to help readers choose pieces that look good and work every day.</p>
HTML,
            ],
            [
                'title' => 'Modern Moroccan Interior Design: A Practical Room-by-Room Guide',
                'slug' => 'modern-moroccan-interior-design',
                'image' => 'blog-assets/modern-moroccan-material-palette.svg',
                'keywords' => [
                    'modern Moroccan interior design',
                    'Moroccan decor ideas',
                    'zellij tiles',
                    'Moroccan home style',
                    'artisan interior design',
                ],
                'short_description' => 'Create modern Moroccan interior design with zellij, carved wood, wool textiles, brass lighting, and calm contemporary room planning.',
                'meta_title' => 'Modern Moroccan Interior Design Guide',
                'meta_description' => 'Learn modern Moroccan interior design room by room with material palettes, color rules, zellij tips, and practical decor ideas.',
                'description' => <<<'HTML'
<p><strong>Modern Moroccan interior design</strong> works best when heritage materials meet clean planning. The goal is not to turn every room into a themed space. The goal is to let zellij, wood, wool, plaster, brass, and greenery bring soul to a home that still feels easy to live in.</p>
<p>Many people love Moroccan decor ideas but hesitate because they fear the result will look too ornate. The modern approach is simpler: choose one craft hero per room, give it space, and keep the surrounding furniture calm.</p>

<h2>Table of Contents</h2>
<ul>
    <li><a href="#intent">Keyword and search intent</a></li>
    <li><a href="#palette">The modern Moroccan palette</a></li>
    <li><a href="#rooms">Room-by-room design guide</a></li>
    <li><a href="#materials">Materials and maintenance</a></li>
    <li><a href="#mistakes">Common mistakes</a></li>
    <li><a href="#faq">FAQ</a></li>
</ul>

<h2 id="intent">Modern Moroccan Interior Design: Keyword and Intent</h2>
<p><strong>Primary keyword:</strong> modern Moroccan interior design.</p>
<p><strong>Semantic variants:</strong> Moroccan decor ideas, Moroccan home style, zellij tiles, artisan interior design, Moroccan living room decor.</p>
<p>The intent is inspiration plus planning. Readers want images and ideas, but they also need practical guidance on color, proportion, maintenance, and what to buy first.</p>

<h2 id="palette">Build the Room With the 60-30-10 Palette</h2>
<p>A strong Moroccan room usually has rhythm: a calm base, a warm natural layer, and a crafted accent. The 60-30-10 rule keeps that rhythm balanced.</p>
<table>
    <thead>
        <tr>
            <th>Layer</th>
            <th>Use</th>
            <th>Examples</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>60%</td>
            <td>Quiet base</td>
            <td>Limewash, warm white, sand, soft greige</td>
        </tr>
        <tr>
            <td>30%</td>
            <td>Natural warmth</td>
            <td>Walnut wood, woven rugs, leather, clay</td>
        </tr>
        <tr>
            <td>10%</td>
            <td>Craft accent</td>
            <td>Zellij, brass lantern, embroidered cushion, carved mirror</td>
        </tr>
    </tbody>
</table>
<p>This ratio prevents the room from becoming too busy. It also makes artisan pieces feel more valuable because they are not competing with every other surface.</p>

<h2 id="rooms">Room-by-Room Guide</h2>
<h3>Living room</h3>
<p>Start with a low, comfortable sofa, a wool rug, and one strong craft detail. That detail can be a carved wood coffee table, a brass floor lamp, or a zellij-topped side table. Keep the largest upholstery simple so the handcrafted surface has room to shine.</p>
<p>If your living room is compact, read our <a href="small-living-room-interior-design">small living room interior design guide</a> before choosing the sofa size. Moroccan style loves texture, but small rooms still need breathing space.</p>
<h3>Kitchen</h3>
<p>Zellij works beautifully as a backsplash because its surface catches light. The handmade variation is the point; do not expect every tile to look identical. Use a quiet countertop if the backsplash is glossy or colorful.</p>
<p>Morocco's Ministry of Youth, Culture and Communication has described ongoing efforts around the cultural recognition of the art of zellige from Fez and Tetouan, which reinforces why this material carries heritage value beyond trend: <a href="https://mjcc.gov.ma/fr/sale-lancement-officiel-du-projet-dinscription-de-lart-du-zellige-de-fes-et-tetouan-sur-la-liste-du-patrimoine-culturel-immateriel-de-lunesco/" target="_blank" rel="noopener">zellige cultural heritage project</a>.</p>
<h3>Bedroom</h3>
<p>Use Moroccan design more quietly in a bedroom. A carved headboard, soft wool blanket, plaster-look wall, or two amber bedside lamps can create warmth without disturbing rest.</p>
<p>For sleep-focused choices, combine this article with our <a href="bedroom-lighting-ideas">bedroom lighting ideas</a>.</p>
<h3>Entryway</h3>
<p>An entryway is the easiest place to use a bold Moroccan moment. Try a patterned tile strip, arched mirror, small bench, and one basket for shoes or scarves. The entry should feel generous, not crowded.</p>

<h2 id="materials">Materials That Age Well</h2>
<p>Modern Moroccan interior design depends on materials that gain character. Zellij, wool, wood, brass, leather, and lime plaster all age differently, which is why they feel alive.</p>
<ul>
    <li><strong>Zellij:</strong> best for backsplashes, powder rooms, niches, and accent walls.</li>
    <li><strong>Carved wood:</strong> useful for mirrors, doors, side tables, and console details.</li>
    <li><strong>Wool textiles:</strong> ideal for rugs, throws, cushions, and acoustic softness.</li>
    <li><strong>Brass:</strong> works well in lighting, handles, trays, and small accents.</li>
    <li><strong>Clay and ceramic:</strong> strong for vases, bowls, planters, and table styling.</li>
</ul>

<h2 id="mistakes">Common Mistakes to Avoid</h2>
<h3>Using too many patterns at once</h3>
<p>Pattern needs a quiet neighbor. If the rug is expressive, keep the sofa plain. If the tile is rich, keep the counter simple.</p>
<h3>Buying decor before planning scale</h3>
<p>A lantern that looks perfect online may be too small above a dining table or too large in a hallway. Measure height, width, and sight lines first.</p>
<h3>Choosing imitation without checking quality</h3>
<p>There are good modern interpretations of Moroccan materials, but check texture, finish, edge quality, and installation needs. The cheapest lookalike often fails because the surface has no depth.</p>

<h2 id="faq">FAQ</h2>
<h3>Can Moroccan design be minimalist?</h3>
<p>Yes. Minimalist Moroccan design uses fewer objects but better texture: plaster walls, one rug, one ceramic piece, one warm metal, and natural light.</p>
<h3>What colors work best in a modern Moroccan home?</h3>
<p>Warm white, sand, clay, olive, deep teal, terracotta, walnut, and brass are reliable. Use saturated colors as accents rather than covering every surface.</p>
<h3>Is zellij practical for modern homes?</h3>
<p>Yes, when installed by someone who understands handmade tile variation. It is best used where its irregularity is celebrated, not where a perfectly flat industrial surface is expected.</p>

<h2>Author Note</h2>
<p><strong>Written by the Mayush Design Editorial Team.</strong> We focus on interiors that respect Moroccan craft while staying practical for real homes, daily cleaning, budgets, and long-term use.</p>
HTML,
            ],
            [
                'title' => 'Bedroom Lighting Ideas: A Layered Interior Design Plan for Better Sleep',
                'slug' => 'bedroom-lighting-ideas',
                'image' => 'blog-assets/bedroom-lighting-layer-plan.svg',
                'keywords' => [
                    'bedroom lighting ideas',
                    'layered bedroom lighting',
                    'warm bedroom lights',
                    'sleep friendly bedroom',
                    'interior lighting design',
                ],
                'short_description' => 'Plan bedroom lighting ideas with ambient, task, and accent layers so your room feels beautiful at night and supports better sleep.',
                'meta_title' => 'Bedroom Lighting Ideas for Better Sleep',
                'meta_description' => 'Use these bedroom lighting ideas to layer warm light, task lamps, dimmers, and blackout control for a calmer sleep-friendly room.',
                'description' => <<<'HTML'
<p><strong>Bedroom lighting ideas</strong> should do two jobs at once: make the room beautiful when you are awake and help the room become quiet when you are ready to sleep. A single bright ceiling bulb cannot do both.</p>
<p>The best bedroom lighting plan uses layers. You need ambient light for general movement, task light for reading or dressing, accent light for mood, and darkness control for sleep.</p>

<h2>Table of Contents</h2>
<ul>
    <li><a href="#intent">Keyword and search intent</a></li>
    <li><a href="#layers">The three lighting layers</a></li>
    <li><a href="#sleep">Sleep-friendly light choices</a></li>
    <li><a href="#room-plan">Bedroom lighting plan by room size</a></li>
    <li><a href="#shopping">Shopping checklist</a></li>
    <li><a href="#faq">FAQ</a></li>
</ul>

<h2 id="intent">Bedroom Lighting Ideas: Keyword and Intent</h2>
<p><strong>Primary keyword:</strong> bedroom lighting ideas.</p>
<p><strong>Semantic variants:</strong> layered bedroom lighting, warm bedroom lights, sleep friendly bedroom, interior lighting design, bedside lamps.</p>
<p>The intent is mixed: readers want inspiration, but they also need practical decisions about lamp height, bulb temperature, dimmers, blackout curtains, and where to place each light.</p>

<h2 id="layers">Use Three Layers Instead of One Bright Light</h2>
<h3>1. Ambient lighting</h3>
<p>Ambient lighting is the room's general light. It can come from a ceiling fixture, recessed downlights, a pendant, or indirect cove lighting. In a bedroom, ambient light should be dimmable whenever possible.</p>
<h3>2. Task lighting</h3>
<p>Task lighting helps you read, dress, fold laundry, or use a vanity. Bedside lamps should usually sit close to shoulder height when you are seated in bed. If the shade is too low, the light hits the pillow. If it is too high, it shines into your eyes.</p>
<h3>3. Accent lighting</h3>
<p>Accent lighting creates the soft evening mood: a small lamp on a dresser, a warm LED strip behind a headboard, or a wall washer on textured plaster. Accent light is not for visibility. It is for atmosphere.</p>

<h2 id="sleep">Choose Sleep-Friendly Light</h2>
<p>Sleep Foundation notes that light and darkness influence the body's sleep-wake rhythm, and that lower light levels before bed can support a calmer sleep environment: <a href="https://www.sleepfoundation.org/bedroom-environment" target="_blank" rel="noopener">bedroom environment guidance</a>.</p>
<p>That does not mean your bedroom should be dark all evening. It means bright, cool light should fade as the night gets closer.</p>
<table>
    <thead>
        <tr>
            <th>Time</th>
            <th>Best Lighting</th>
            <th>Why It Works</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Morning</td>
            <td>Natural light and bright general light</td>
            <td>Helps the room feel active and fresh</td>
        </tr>
        <tr>
            <td>Evening</td>
            <td>Warm bedside lamps and dim accent light</td>
            <td>Supports relaxation and reduces glare</td>
        </tr>
        <tr>
            <td>Night</td>
            <td>Dark room or very low amber nightlight</td>
            <td>Protects sleep while allowing safe movement</td>
        </tr>
    </tbody>
</table>
<p>For bulb choice, ENERGY STAR explains that LED lighting can be efficient, versatile, and available in many forms: <a href="https://www.energystar.gov/products/learn-about-led-lighting" target="_blank" rel="noopener">LED lighting basics</a>. For bedrooms, choose warm color temperatures for evening use, often around 2200K to 3000K.</p>

<h2 id="room-plan">Bedroom Lighting Plan by Room Size</h2>
<h3>Small bedroom</h3>
<p>Use wall-mounted bedside sconces or narrow lamps to free the nightstand. Add one dimmable ceiling light and one warm accent lamp on a dresser. Avoid floor lamps if they block wardrobe doors or circulation.</p>
<h3>Medium bedroom</h3>
<p>Use a ceiling fixture, two bedside lamps, and one accent light. If the bed wall has texture, light it softly from the side. This gives the room depth without adding clutter.</p>
<h3>Large bedroom</h3>
<p>Divide lighting by zone: bed, reading chair, wardrobe, and vanity. Large bedrooms often feel cold at night because the light is too centralized. Use several low lights instead of one strong source.</p>

<h2 id="shopping">Bedroom Lighting Shopping Checklist</h2>
<ul>
    <li>Choose dimmable fixtures where possible.</li>
    <li>Use warm bulbs for evening lamps.</li>
    <li>Check lamp height from the mattress, not from the floor.</li>
    <li>Add blackout curtains if exterior light enters the room.</li>
    <li>Use covered shades or diffusers to reduce direct glare.</li>
    <li>Put switches where you actually use them: entrance, bedside, and vanity if needed.</li>
</ul>

<h2>Connect the Bedroom to the Rest of the Home</h2>
<p>If your bedroom is part of a smaller apartment, our <a href="small-living-room-interior-design">small living room interior design guide</a> can help you keep storage and circulation consistent across rooms. For a warmer crafted look, borrow material ideas from our <a href="modern-moroccan-interior-design">modern Moroccan interior design guide</a>, especially brass lamps, plaster textures, and wool textiles.</p>

<h2 id="faq">FAQ</h2>
<h3>What is the best light color for a bedroom?</h3>
<p>Warm white is usually best for evening bedrooms. Cooler white can work for wardrobes or cleaning, but it should be separate from the lights used before sleep.</p>
<h3>Are LED lights good for bedrooms?</h3>
<p>Yes, if you choose the right color temperature, brightness, and dimmer compatibility. LEDs are flexible, efficient, and available in fixtures for ambient, task, and accent lighting.</p>
<h3>How many lamps should a bedroom have?</h3>
<p>Most bedrooms need at least two bedside lights and one general light. Larger rooms may need an additional accent lamp, wardrobe light, or reading lamp.</p>

<h2>Author Note</h2>
<p><strong>Written by the Mayush Design Editorial Team.</strong> Our lighting recommendations combine interior design planning with everyday habits: reading, dressing, relaxing, cleaning, and sleeping.</p>
HTML,
            ],
        ];
    }
}
