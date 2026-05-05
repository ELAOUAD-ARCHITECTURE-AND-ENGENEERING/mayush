<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use App\Models\Blog;
use App\Models\BlogSubscriberLog;
use App\Models\BusinessSetting;
use App\Models\Product;
use App\Models\Shop;
use App\Http\Requests\BlogSubscribeRequest;
use App\Services\Blog\BlogContentSanitizerService;
use App\Services\Blog\BlogEmailService;
use App\Services\Blog\BlogProductMatcherService;
use App\Services\Blog\BlogSchemaService;
use App\Services\Blog\BlogSettingsService;
use App\Services\Blog\BlogTocService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Stichoza\GoogleTranslate\GoogleTranslate;

class BlogController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_blogs'])->only('index');
        $this->middleware(['permission:add_blog'])->only('create');
        $this->middleware(['permission:edit_blog'])->only('edit');
        $this->middleware(['permission:delete_blog'])->only('destroy');
        $this->middleware(['permission:publish_blog'])->only('change_status');
        $this->middleware(['permission:view_blogs'])->only('conversion_settings', 'conversion_subscribers', 'export_conversion_subscribers');
        $this->middleware(['permission:edit_blog'])->only('update_conversion_settings');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_search = null;
        $blogs = Blog::orderBy('created_at', 'desc');

        if ($request->search != null) {
            $blogs = $blogs->where('title', 'like', '%' . $request->search . '%');
            $sort_search = $request->search;
        }

        $blogs = $blogs->paginate(15);

        return view('backend.blog_system.blog.index', compact('blogs', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $blog_categories = BlogCategory::all();
        $assignable_products = $this->blogAssignableProducts();
        $shops = Shop::orderBy('name')->limit(300)->get();

        return view('backend.blog_system.blog.create', compact('blog_categories', 'assignable_products', 'shops'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'category_id' => 'required',
            'title' => 'required|max:255',
        ]);

        $blog = new Blog;

        $blog->category_id = $request->category_id;
        $blog->title = $request->title;
        $blog->banner = $request->banner;
        $blog->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $blog->short_description = $request->short_description;
        $blog->description = $request->description;

        $blog->meta_title = $request->meta_title;
        $blog->meta_img = $request->meta_img;
        $blog->meta_description = $request->meta_description;
        $blog->meta_keywords = $request->meta_keywords;
        $this->fillBlogConversionFields($blog, $request);

        $blog->save();
        $this->syncBlogProducts($blog, $request);

        flash(translate('Blog post has been created successfully'))->success();
        return redirect()->route('blog.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {}

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $blog = Blog::with('products')->find($id);
        $blog_categories = BlogCategory::all();
        $assignable_products = $this->blogAssignableProducts();
        $shops = Shop::orderBy('name')->limit(300)->get();

        return view('backend.blog_system.blog.edit', compact('blog', 'blog_categories', 'assignable_products', 'shops'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required',
            'title' => 'required|max:255',
        ]);

        $blog = Blog::find($id);

        $blog->category_id = $request->category_id;
        $blog->title = $request->title;
        $blog->banner = $request->banner;
        $blog->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->slug));
        $blog->short_description = $request->short_description;
        $blog->description = $request->description;

        $blog->meta_title = $request->meta_title;
        $blog->meta_img = $request->meta_img;
        $blog->meta_description = $request->meta_description;
        $blog->meta_keywords = $request->meta_keywords;
        $this->fillBlogConversionFields($blog, $request);

        $blog->save();
        $this->syncBlogProducts($blog, $request);

        flash(translate('Blog post has been updated successfully'))->success();
        return redirect()->route('blog.index');
    }

    public function change_status(Request $request)
    {
        $blog = Blog::find($request->id);
        $blog->{$request->field} = $request->status;

        $blog->save();
        return 1;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Blog::find($id)->delete();
        return back();
    }

    public function conversion_settings()
    {
        return view('backend.blog_system.conversion.settings');
    }

    public function update_conversion_settings(Request $request)
    {
        $validated = $request->validate([
            'blog_enable_product_embeds' => ['nullable', 'boolean'],
            'blog_products_per_embed' => ['required', 'integer', 'min:1', 'max:12'],
            'blog_enable_hero' => ['nullable', 'boolean'],
            'blog_featured_article_id' => ['nullable', 'integer', 'exists:blogs,id'],
            'blog_hero_cta_text' => ['nullable', 'string', 'max:80'],
            'blog_articles_per_page' => ['nullable', 'integer', 'min:3', 'max:48'],
            'blog_enable_category_tabs' => ['nullable', 'boolean'],
            'blog_enable_read_time' => ['nullable', 'boolean'],
            'blog_enable_product_count_badge' => ['nullable', 'boolean'],
            'blog_enable_scroll_progress' => ['nullable', 'boolean'],
            'blog_product_embed_cache_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'blog_enable_article_schema' => ['nullable', 'boolean'],
            'blog_enable_product_schema' => ['nullable', 'boolean'],
            'blog_enable_sidebar_products' => ['nullable', 'boolean'],
            'blog_sidebar_products_count' => ['nullable', 'integer', 'min:1', 'max:8'],
            'blog_enable_post_read_products' => ['nullable', 'boolean'],
            'blog_post_read_products_count' => ['nullable', 'integer', 'min:1', 'max:12'],
            'blog_enable_lazy_product_loading' => ['nullable', 'boolean'],
            'blog_enable_share_bar' => ['nullable', 'boolean'],
            'blog_enable_table_of_contents' => ['nullable', 'boolean'],
            'blog_email_enable_listing_inline' => ['nullable', 'boolean'],
            'blog_email_listing_interval' => ['nullable', 'integer', 'min:1', 'max:12'],
            'blog_email_enable_mid_article' => ['nullable', 'boolean'],
            'blog_email_enable_sidebar' => ['nullable', 'boolean'],
            'blog_email_enable_post_read' => ['nullable', 'boolean'],
            'blog_email_provider' => ['required', 'in:local,mailchimp,klaviyo,webhook'],
            'blog_webhook_url' => ['nullable', 'url', 'max:500'],
            'blog_mailchimp_api_key' => ['nullable', 'string', 'max:255'],
            'blog_mailchimp_list_id' => ['nullable', 'string', 'max:255'],
            'blog_klaviyo_api_key' => ['nullable', 'string', 'max:255'],
            'blog_klaviyo_list_id' => ['nullable', 'string', 'max:255'],
            'blog_klaviyo_revision' => ['nullable', 'date_format:Y-m-d'],
            'blog_email_success_message' => ['nullable', 'string', 'max:255'],
            'blog_enable_vendor_cta' => ['nullable', 'boolean'],
            'blog_enable_related_articles' => ['nullable', 'boolean'],
            'blog_related_articles_count' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        foreach ($this->blogConversionSettingKeys() as $key => $default) {
            if (in_array($key, $this->blogConversionSecretKeys(), true)) {
                if ($request->filled($key)) {
                    BusinessSetting::updateOrCreate(
                        ['type' => $key],
                        ['value' => BlogSettingsService::encryptSecret($request->input($key))]
                    );
                }

                continue;
            }

            BusinessSetting::updateOrCreate(
                ['type' => $key],
                ['value' => (string) ($validated[$key] ?? $default)]
            );
        }

        Cache::forget('business_settings');

        flash(translate('Blog conversion settings updated successfully'))->success();
        return back();
    }

    public function conversion_subscribers(Request $request)
    {
        $logs = $this->filteredSubscriberLogs($request)->paginate(20);

        return view('backend.blog_system.conversion.subscribers', compact('logs'));
    }

    public function export_conversion_subscribers(Request $request)
    {
        $rows = $this->filteredSubscriberLogs($request)->limit(5000)->get();
        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['email', 'placement', 'blog_title', 'provider', 'provider_status', 'subscribed_at', 'ip_address']);

        foreach ($rows as $row) {
            fputcsv($csv, [
                $row->email,
                $row->placement,
                $row->blog_title,
                $row->provider,
                $row->provider_status,
                optional($row->subscribed_at)->toDateTimeString(),
                $row->ip_address,
            ]);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="blog-subscriber-logs.csv"',
        ]);
    }


    public function all_blog(Request $request, BlogSettingsService $settingsService)
    {
        $blogSettings = $settingsService->all();
        $selected_categories = array();
        $search = null;
        $blogs = Blog::published()->with(['category', 'translations', 'products']);
        $blogCategoriesQuery = BlogCategory::query()->orderBy('category_name');
        if (Schema::hasColumn('blog_categories', 'status')) {
            $blogCategoriesQuery->where('status', 1);
        }
        $blogCategories = $blogCategoriesQuery->get();

        if ($request->has('search')) {
            $search = $request->search;;
            $blogs->where(function ($q) use ($search) {
                foreach (explode(' ', trim($search)) as $word) {
                    $q->where('title', 'like', '%' . $word . '%')
                        ->orWhere('short_description', 'like', '%' . $word . '%');
                }
            });

            $case1 = $search . '%';
            $case2 = '%' . $search . '%';

            $blogs->orderByRaw("CASE 
                WHEN title LIKE '$case1' THEN 1 
                WHEN title LIKE '$case2' THEN 2 
                ELSE 3 
                END");
        }

        if ($request->filled('category')) {
            $selected_categories = [$request->category];
            $blogs->byCategory($request->category);
        }

        if ($request->has('selected_categories')) {
            $selected_categories = $request->selected_categories;
            $blog_categories = BlogCategory::whereIn('slug', $selected_categories)->pluck('id')->toArray();

            $blogs->whereIn('category_id', $blog_categories);
        }

        $featuredBlog = null;
        if ($blogSettings['hero_enabled']) {
            $featuredBlogQuery = Blog::published()->with(['category', 'translations', 'products']);

            if ($blogSettings['featured_article_id'] > 0) {
                $featuredBlogQuery->where('id', $blogSettings['featured_article_id']);
            } else {
                $featuredBlogQuery->featured();
            }

            $featuredBlog = $featuredBlogQuery
                ->orderBy('published_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $blogs = $blogs
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($blogSettings['articles_per_page'])
            ->withQueryString();

        $recent_blogs = Blog::published()->with(['category', 'translations'])->orderBy('published_at', 'desc')->orderBy('created_at', 'desc')->limit(9)->get();

        return view("frontend.blog.listing", compact('blogs', 'selected_categories', 'search', 'recent_blogs', 'blogSettings', 'blogCategories', 'featuredBlog'));
    }

    public function blog_details(
        $slug,
        BlogContentSanitizerService $sanitizer,
        BlogProductMatcherService $productMatcher,
        BlogSchemaService $schemaService,
        BlogSettingsService $settingsService,
        BlogTocService $tocService
    )
    {
        $blog = Blog::published()->with(['category', 'author', 'shop', 'tags', 'translations', 'products'])->where('slug', $slug)->firstOrFail();
        $recent_blogs = Blog::published()->with(['category', 'translations'])->where('id', '!=', $blog->id)->orderBy('published_at', 'desc')->orderBy('created_at', 'desc')->limit(9)->get();
        $related_blogs = Blog::published()
            ->with(['category', 'translations'])
            ->where('id', '!=', $blog->id)
            ->where(function ($query) use ($blog) {
                $query->where('category_id', $blog->category_id);

                if ($blog->tags->isNotEmpty()) {
                    $query->orWhereHas('tags', function ($tagQuery) use ($blog) {
                        $tagQuery->whereIn('tags.id', $blog->tags->pluck('id'));
                    });
                }
            })
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $blogSettings = $settingsService->all();
        $sanitizedDescription = $sanitizer->sanitize($blog->getTranslation('description'));
        $tocResult = $blogSettings['table_of_contents_enabled']
            ? $tocService->parse($sanitizedDescription)
            : ['content' => $sanitizedDescription, 'toc' => []];
        $sanitizedBlogDescription = $tocResult['content'];
        $blogToc = $tocResult['toc'];
        $articleProducts = $blogSettings['product_embeds_enabled']
            ? $productMatcher->productsFor($blog, 'manual', $blogSettings['products_per_embed'])
            : collect();
        $sidebarProducts = $blogSettings['product_embeds_enabled'] && $blogSettings['sidebar_products_enabled']
            ? $productMatcher->productsFor($blog, 'sidebar', $blogSettings['sidebar_products_count'])
            : collect();
        $postReadProducts = $blogSettings['product_embeds_enabled'] && $blogSettings['post_read_products_enabled']
            ? $productMatcher->productsFor($blog, 'post_read', $blogSettings['post_read_products_count'])
            : collect();
        $blogProductSchemas = $blogSettings['product_embeds_enabled'] && $blogSettings['product_schema_enabled']
            ? $schemaService->productSchemas($articleProducts->merge($sidebarProducts)->merge($postReadProducts)->unique('id'))
            : [];

        return view("frontend.blog.details", compact('blog', 'recent_blogs', 'related_blogs', 'sanitizedBlogDescription', 'blogToc', 'articleProducts', 'sidebarProducts', 'postReadProducts', 'blogProductSchemas', 'blogSettings'));
    }

    public function subscribe(BlogSubscribeRequest $request, BlogEmailService $emailService)
    {
        $result = $emailService->subscribe($request->validated(), $request);

        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return back()->with(
            $result['success'] ? 'blog_subscribe_success' : 'blog_subscribe_error',
            $result['message']
        );
    }

    public function generateSlug(Request $request)
    {
        $translator = new GoogleTranslate('en'); // Target language
        $translated = $translator->translate($request->title); // auto detects source

        // Slugify the translated string
        $slug = Str::slug($translated);

        return response()->json(['slug' => $slug]);
    }

    private function filteredSubscriberLogs(Request $request)
    {
        return BlogSubscriberLog::query()
            ->when($request->filled('email'), fn ($query) => $query->where('email', 'like', '%' . $request->email . '%'))
            ->when($request->filled('placement'), fn ($query) => $query->where('placement', $request->placement))
            ->when($request->filled('provider'), fn ($query) => $query->where('provider', $request->provider))
            ->orderBy('subscribed_at', 'desc')
            ->orderBy('created_at', 'desc');
    }

    private function blogConversionSettingKeys(): array
    {
        return [
            'blog_enable_product_embeds' => 0,
            'blog_products_per_embed' => 4,
            'blog_enable_hero' => 0,
            'blog_featured_article_id' => '',
            'blog_hero_cta_text' => translate('Read guide'),
            'blog_articles_per_page' => 12,
            'blog_enable_category_tabs' => 0,
            'blog_enable_read_time' => 0,
            'blog_enable_product_count_badge' => 0,
            'blog_enable_scroll_progress' => 0,
            'blog_product_embed_cache_minutes' => 15,
            'blog_enable_article_schema' => 0,
            'blog_enable_product_schema' => 0,
            'blog_enable_sidebar_products' => 0,
            'blog_sidebar_products_count' => 3,
            'blog_enable_post_read_products' => 0,
            'blog_post_read_products_count' => 4,
            'blog_enable_lazy_product_loading' => 0,
            'blog_enable_share_bar' => 0,
            'blog_enable_table_of_contents' => 0,
            'blog_email_enable_listing_inline' => 0,
            'blog_email_listing_interval' => 3,
            'blog_email_enable_mid_article' => 0,
            'blog_email_enable_sidebar' => 0,
            'blog_email_enable_post_read' => 0,
            'blog_email_provider' => 'local',
            'blog_webhook_url' => '',
            'blog_mailchimp_api_key' => '',
            'blog_mailchimp_list_id' => '',
            'blog_klaviyo_api_key' => '',
            'blog_klaviyo_list_id' => '',
            'blog_klaviyo_revision' => '2026-04-15',
            'blog_email_success_message' => translate("You're in! Check your inbox."),
            'blog_enable_vendor_cta' => 0,
            'blog_enable_related_articles' => 0,
            'blog_related_articles_count' => 3,
        ];
    }

    private function blogConversionSecretKeys(): array
    {
        return [
            'blog_mailchimp_api_key',
            'blog_klaviyo_api_key',
        ];
    }

    private function blogAssignableProducts()
    {
        return Product::query()
            ->with(['thumbnail', 'user.shop'])
            ->isApprovedPublished()
            ->where('digital', 0)
            ->orderBy('name')
            ->limit(300)
            ->get();
    }

    private function fillBlogConversionFields(Blog $blog, Request $request): void
    {
        $blog->hero_image = $request->hero_image;
        $blog->badge_type = $request->badge_type ?: null;
        $blog->custom_badge_text = $request->custom_badge_text;
        $blog->is_featured = $request->has('is_featured') ? 1 : 0;
        $blog->canonical_url = $request->canonical_url;
        $blog->schema_enabled = $request->has('schema_enabled') ? 1 : 0;
        $blog->shop_id = $request->shop_id ?: null;
        $blog->vendor_quote = $request->vendor_quote;
    }

    private function syncBlogProducts(Blog $blog, Request $request): void
    {
        $productIds = collect($request->input('product_ids', []))
            ->filter()
            ->unique()
            ->values();

        $safeProductIds = Product::query()
            ->isApprovedPublished()
            ->whereIn('id', $productIds)
            ->pluck('id')
            ->all();

        $sync = [];
        foreach ($safeProductIds as $index => $productId) {
            $sync[$productId] = [
                'placement' => 'manual',
                'sort_order' => $index + 1,
            ];
        }

        $blog->products()->sync($sync);
    }
}
