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
use App\Models\User;
use App\Http\Requests\BlogSubscribeRequest;
use App\Services\Blog\BlogContentBlockService;
use App\Services\Blog\BlogContentSanitizerService;
use App\Services\Blog\BlogEmailService;
use App\Services\Blog\BlogProductMatcherService;
use App\Services\Blog\BlogSchemaService;
use App\Services\Blog\BlogSettingsService;
use App\Services\Blog\BlogTocService;
use App\Services\Blog\BlogWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
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
        $this->middleware(['permission:publish_blog'])->only('change_status', 'publish', 'archive');
        $this->middleware(['permission:review_blog'])->only('request_changes');
        $this->middleware(['permission:manage_blog_authors'])->only('authors', 'assign_author', 'remove_author');
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
        $blogs = Blog::with(['category', 'author'])->orderBy('created_at', 'desc');

        if ($request->search != null) {
            $blogs = $blogs->where('title', 'like', '%' . $request->search . '%');
            $sort_search = $request->search;
        }

        if ($request->filled('workflow_status')) {
            $blogs->where('workflow_status', $request->workflow_status);
        }

        if ($request->filled('author_id')) {
            $blogs->where('user_id', $request->author_id);
        }

        $blogs = $blogs->paginate(15);
        $authors = $this->blogAuthors()->get(['id', 'name', 'email']);
        $workflowStatuses = $this->workflowStatuses();

        return view('backend.blog_system.blog.index', compact('blogs', 'sort_search', 'authors', 'workflowStatuses'));
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
        $authors = $this->blogAuthors()->get(['id', 'name', 'email']);

        return view('backend.blog_system.blog.create', compact('blog_categories', 'assignable_products', 'shops', 'authors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, BlogContentBlockService $blockService, BlogWorkflowService $workflow)
    {

        $request->validate([
            'category_id' => ['required', 'integer', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:blogs,slug'],
            'short_description' => ['required', 'string'],
            'workflow_action' => ['nullable', Rule::in(['draft', 'submit', 'publish'])],
            'published_at' => ['nullable', 'date'],
        ]);

        $blog = new Blog;

        $this->fillBlogFields($blog, $request, $blockService);
        $blog->user_id = $request->input('user_id') ?: Auth::id();
        $this->applyWorkflowAction($blog, $request, $workflow);

        $blog->save();
        $this->syncBlogProducts($blog, $request);
        $workflow->saveVersion($blog, $request->user(), $blog->workflow_status);

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
        $blog = Blog::with(['products', 'versions.actor'])->findOrFail($id);
        $blog_categories = BlogCategory::all();
        $assignable_products = $this->blogAssignableProducts();
        $shops = Shop::orderBy('name')->limit(300)->get();
        $authors = $this->blogAuthors()->get(['id', 'name', 'email']);

        return view('backend.blog_system.blog.edit', compact('blog', 'blog_categories', 'assignable_products', 'shops', 'authors'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, BlogContentBlockService $blockService, BlogWorkflowService $workflow)
    {
        $request->validate([
            'category_id' => ['required', 'integer', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($id)],
            'short_description' => ['required', 'string'],
            'workflow_action' => ['nullable', Rule::in(['draft', 'submit', 'publish'])],
            'published_at' => ['nullable', 'date'],
        ]);

        $blog = Blog::findOrFail($id);

        $this->fillBlogFields($blog, $request, $blockService);
        $this->applyWorkflowAction($blog, $request, $workflow);
        $blog->save();
        $this->syncBlogProducts($blog, $request);
        $workflow->saveVersion($blog, $request->user(), $blog->workflow_status);

        flash(translate('Blog post has been updated successfully'))->success();
        return redirect()->route('blog.index');
    }

    public function change_status(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:blogs,id'],
            'field' => ['required', Rule::in(['status', 'news', 'event', 'going_on'])],
            'status' => ['required', 'boolean'],
        ]);

        $blog = Blog::findOrFail($validated['id']);
        $blog->{$validated['field']} = (int) $validated['status'];

        if ($validated['field'] === 'status') {
            $blog->workflow_status = (int) $validated['status'] === 1
                ? BlogWorkflowService::PUBLISHED
                : BlogWorkflowService::DRAFT;
            $blog->published_at = (int) $validated['status'] === 1 ? ($blog->published_at ?: now()) : $blog->published_at;
        }

        $blog->save();
        return 1;
    }

    public function publish($id, BlogWorkflowService $workflow)
    {
        $blog = Blog::findOrFail($id);
        $workflow->publish($blog, Auth::user());
        $blog->save();
        $workflow->saveVersion($blog, Auth::user(), 'published');

        flash(translate('Blog post has been published successfully'))->success();
        return back();
    }

    public function archive($id, BlogWorkflowService $workflow)
    {
        $blog = Blog::findOrFail($id);
        $workflow->archive($blog, Auth::user());
        $blog->save();
        $workflow->saveVersion($blog, Auth::user(), 'archived');

        flash(translate('Blog post has been archived successfully'))->success();
        return back();
    }

    public function request_changes(Request $request, $id, BlogWorkflowService $workflow)
    {
        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $blog = Blog::findOrFail($id);
        $workflow->requestChanges($blog, Auth::user(), $validated['review_note'] ?? null);
        $blog->save();
        $workflow->saveVersion($blog, Auth::user(), 'changes_requested');

        flash(translate('Changes requested from the author'))->success();
        return back();
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

    public function authors(Request $request)
    {
        $authors = $this->blogAuthors()
            ->withCount('blogs')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($innerQuery) use ($request) {
                    $innerQuery->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $availableUsers = User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'author'))
            ->orderBy('name')
            ->limit(200)
            ->get(['id', 'name', 'email']);

        return view('backend.blog_system.blog.authors', compact('authors', 'availableUsers'));
    }

    public function assign_author(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        Role::findOrCreate('author', 'web');
        User::findOrFail($validated['user_id'])->assignRole('author');

        flash(translate('Author role has been assigned successfully'))->success();
        return back();
    }

    public function remove_author($id)
    {
        if (Role::where('name', 'author')->where('guard_name', 'web')->exists()) {
            User::findOrFail($id)->removeRole('author');
        }

        flash(translate('Author role has been removed successfully'))->success();
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
                WHEN title LIKE ? THEN 1
                WHEN title LIKE ? THEN 2
                ELSE 3
                END", [$case1, $case2]);
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
        return $this->blogDetailView($blog, $sanitizer, $productMatcher, $schemaService, $settingsService, $tocService);
    }

    public function preview(
        $id,
        BlogContentSanitizerService $sanitizer,
        BlogProductMatcherService $productMatcher,
        BlogSchemaService $schemaService,
        BlogSettingsService $settingsService,
        BlogTocService $tocService
    ) {
        $blog = Blog::with(['category', 'author', 'shop', 'tags', 'translations', 'products'])->findOrFail($id);

        if (!$this->canPreview($blog)) {
            abort(403);
        }

        return $this->blogDetailView($blog, $sanitizer, $productMatcher, $schemaService, $settingsService, $tocService, true);
    }

    private function blogDetailView(
        Blog $blog,
        BlogContentSanitizerService $sanitizer,
        BlogProductMatcherService $productMatcher,
        BlogSchemaService $schemaService,
        BlogSettingsService $settingsService,
        BlogTocService $tocService,
        bool $isPreview = false
    ) {
        $blogSettings = $settingsService->all();
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
            ->limit((int) ($blogSettings['related_articles_count'] ?? 3))
            ->get();

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

        return view("frontend.blog.details", compact('blog', 'recent_blogs', 'related_blogs', 'sanitizedBlogDescription', 'blogToc', 'articleProducts', 'sidebarProducts', 'postReadProducts', 'blogProductSchemas', 'blogSettings', 'isPreview'));
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

    private function blogAuthors()
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'author')->where('guard_name', 'web');
            })
            ->orderBy('name');
    }

    private function fillBlogFields(Blog $blog, Request $request, BlogContentBlockService $blockService): void
    {
        $blocks = $blockService->normalize($request->input('content_blocks'));

        $blog->category_id = $request->category_id;
        $blog->title = $request->title;
        $blog->banner = $request->banner;
        $blog->slug = Str::slug($request->slug);
        $blog->short_description = $request->short_description;
        $blog->content_blocks = $blocks;
        $blog->description = $blockService->compileHtml($blocks, $request->description);
        $blog->published_at = $request->filled('published_at') ? $request->date('published_at') : $blog->published_at;

        if ($request->filled('user_id') && $this->currentUserIsBlogSuperAdmin()) {
            $blog->user_id = $request->user_id;
        }

        $blog->meta_title = $request->meta_title;
        $blog->meta_img = $request->meta_img;
        $blog->meta_description = $request->meta_description;
        $blog->meta_keywords = $request->meta_keywords;
        $this->fillBlogConversionFields($blog, $request);
    }

    private function applyWorkflowAction(Blog $blog, Request $request, BlogWorkflowService $workflow): void
    {
        if (!$request->filled('workflow_action') && $blog->exists) {
            return;
        }

        $action = $request->input('workflow_action', 'draft');

        if ($action === 'publish' && $request->user()->can('publish_blog')) {
            $workflow->publish($blog, $request->user());
            return;
        }

        if ($action === 'submit') {
            $workflow->submitForReview($blog);
            return;
        }

        if (!$blog->exists || $action === 'draft' || $blog->workflow_status !== BlogWorkflowService::PUBLISHED) {
            $workflow->saveDraft($blog);
        }
    }

    private function canPreview(Blog $blog): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $this->currentUserIsBlogSuperAdmin()
            || $user->can('view_blogs')
            || ($blog->user_id === $user->id && $user->can('view_own_blogs'));
    }

    private function currentUserIsBlogSuperAdmin(): bool
    {
        $user = Auth::user();

        return $user && ($user->user_type === 'admin' || $user->hasRole('blog_super_admin') || $user->can('blog_super_admin'));
    }

    private function workflowStatuses(): array
    {
        return [
            BlogWorkflowService::DRAFT => translate('Draft'),
            BlogWorkflowService::SUBMITTED => translate('Submitted'),
            BlogWorkflowService::CHANGES_REQUESTED => translate('Changes requested'),
            BlogWorkflowService::PUBLISHED => translate('Published'),
            BlogWorkflowService::ARCHIVED => translate('Archived'),
        ];
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
