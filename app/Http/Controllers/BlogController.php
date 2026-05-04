<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogCategory;
use App\Models\Blog;
use App\Http\Requests\BlogSubscribeRequest;
use App\Services\Blog\BlogContentSanitizerService;
use App\Services\Blog\BlogEmailService;
use App\Services\Blog\BlogProductMatcherService;
use App\Services\Blog\BlogSettingsService;
use App\Services\Blog\BlogTocService;
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
        return view('backend.blog_system.blog.create', compact('blog_categories'));
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

        $blog->save();

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
        $blog = Blog::find($id);
        $blog_categories = BlogCategory::all();

        return view('backend.blog_system.blog.edit', compact('blog', 'blog_categories'));
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

        $blog->save();

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


    public function all_blog(Request $request, BlogSettingsService $settingsService)
    {
        $selected_categories = array();
        $search = null;
        $blogs = Blog::query();

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

        if ($request->has('selected_categories')) {
            $selected_categories = $request->selected_categories;
            $blog_categories = BlogCategory::whereIn('slug', $selected_categories)->pluck('id')->toArray();

            $blogs->whereIn('category_id', $blog_categories);
        }

        $blogs = $blogs->where('status', 1)->orderBy('created_at', 'desc')->paginate(12);

        $recent_blogs = Blog::where('status', 1)->orderBy('created_at', 'desc')->limit(9)->get();

        $blogSettings = $settingsService->all();

        return view("frontend.blog.listing", compact('blogs', 'selected_categories', 'search', 'recent_blogs', 'blogSettings'));
    }

    public function blog_details(
        $slug,
        BlogContentSanitizerService $sanitizer,
        BlogProductMatcherService $productMatcher,
        BlogSettingsService $settingsService,
        BlogTocService $tocService
    )
    {
        $blog = Blog::published()->with(['category', 'author', 'tags', 'translations', 'products'])->where('slug', $slug)->firstOrFail();
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

        return view("frontend.blog.details", compact('blog', 'recent_blogs', 'related_blogs', 'sanitizedBlogDescription', 'blogToc', 'articleProducts', 'blogSettings'));
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
}
