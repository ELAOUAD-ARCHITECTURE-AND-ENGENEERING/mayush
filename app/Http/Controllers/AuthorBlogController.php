<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Services\Blog\BlogContentBlockService;
use App\Services\Blog\BlogWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthorBlogController extends Controller
{
    public function index()
    {
        $this->authorizeAuthor();

        $blogs = Blog::with('category')
            ->where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        return view('backend.blog_system.author.index', compact('blogs'));
    }

    public function create()
    {
        $this->authorizeAuthor();

        $blog_categories = BlogCategory::all();

        return view('backend.blog_system.author.create', compact('blog_categories'));
    }

    public function store(Request $request, BlogContentBlockService $blockService, BlogWorkflowService $workflow)
    {
        $this->authorizeAuthor();
        $this->validateArticle($request);

        $blog = new Blog();
        $this->fillArticle($blog, $request, $blockService);
        $blog->user_id = Auth::id();
        $this->applyAuthorAction($blog, $request, $workflow);
        $blog->save();
        $workflow->saveVersion($blog, $request->user(), $blog->workflow_status);

        flash(translate('Article has been saved successfully'))->success();
        return redirect()->route('author.blogs.index');
    }

    public function edit($id)
    {
        $this->authorizeAuthor();

        $blog = $this->authorBlog($id);
        $blog_categories = BlogCategory::all();

        return view('backend.blog_system.author.edit', compact('blog', 'blog_categories'));
    }

    public function update(Request $request, $id, BlogContentBlockService $blockService, BlogWorkflowService $workflow)
    {
        $this->authorizeAuthor();
        $this->validateArticle($request, $id);

        $blog = $this->authorBlog($id);
        $this->fillArticle($blog, $request, $blockService);
        $this->applyAuthorAction($blog, $request, $workflow);
        $blog->save();
        $workflow->saveVersion($blog, $request->user(), $blog->workflow_status);

        flash(translate('Article has been updated successfully'))->success();
        return redirect()->route('author.blogs.index');
    }

    public function destroy($id)
    {
        $this->authorizeAuthor();

        $blog = $this->authorBlog($id);

        if ($blog->workflow_status === BlogWorkflowService::PUBLISHED) {
            abort(403);
        }

        $blog->delete();

        flash(translate('Draft article has been deleted successfully'))->success();
        return back();
    }

    private function validateArticle(Request $request, ?int $ignoreId = null): void
    {
        $request->validate([
            'category_id' => ['required', 'integer', 'exists:blog_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($ignoreId)],
            'short_description' => ['required', 'string'],
            'workflow_action' => ['required', Rule::in(['draft', 'submit'])],
        ]);
    }

    private function fillArticle(Blog $blog, Request $request, BlogContentBlockService $blockService): void
    {
        $blocks = $blockService->normalize($request->input('content_blocks'));

        $blog->category_id = $request->category_id;
        $blog->title = $request->title;
        $blog->slug = Str::slug($request->slug);
        $blog->banner = $request->banner;
        $blog->short_description = $request->short_description;
        $blog->content_blocks = $blocks;
        $blog->description = $blockService->compileHtml($blocks, $request->description);
        $blog->meta_title = $request->meta_title;
        $blog->meta_img = $request->meta_img;
        $blog->meta_description = $request->meta_description;
        $blog->meta_keywords = $request->meta_keywords;
    }

    private function applyAuthorAction(Blog $blog, Request $request, BlogWorkflowService $workflow): void
    {
        if ($request->input('workflow_action') === 'submit') {
            $workflow->submitForReview($blog);
            return;
        }

        $workflow->saveDraft($blog);
    }

    private function authorBlog($id): Blog
    {
        return Blog::where('user_id', Auth::id())->findOrFail($id);
    }

    private function authorizeAuthor(): void
    {
        $user = Auth::user();

        if (!$user || (!$user->hasRole('author') && !$user->can('blog_super_admin'))) {
            abort(403);
        }
    }
}
