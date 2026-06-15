<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BlogHtmlPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure permissions and roles exist
        $permission = Permission::firstOrCreate(['name' => 'manage_blog_html', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
    }

    public function test_user_without_permission_cannot_submit_html_blocks()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        Permission::firstOrCreate(['name' => 'add_blog', 'guard_name' => 'web']);
        $user->givePermissionTo('add_blog');
        $category = BlogCategory::create(['category_name' => 'Test Category', 'slug' => 'test-cat']);

        $blocks = [
            [
                'type' => 'html',
                'data' => ['code' => '<script>alert("hacked")</script>']
            ],
            [
                'type' => 'paragraph',
                'data' => ['text' => 'Normal paragraph']
            ]
        ];

        $response = $this->actingAs($user)->post(route('blog.store'), [
            'title' => 'Test Blog',
            'slug' => 'test-blog',
            'category_id' => $category->id,
            'short_description' => 'Short description',
            'content_blocks' => json_encode($blocks),
            'workflow_action' => 'draft',
        ]);

        $response->assertRedirect(route('blog.index'));

        $blog = Blog::where('slug', 'test-blog')->first();
        $this->assertNotNull($blog);

        // HTML block should be discarded or sanitized, but paragraph should be there
        $savedBlocks = $blog->content_blocks;
        
        // Ensure no HTML block was saved
        $htmlBlocks = collect($savedBlocks)->where('type', 'html');
        $this->assertTrue($htmlBlocks->isEmpty());

        $paragraphBlocks = collect($savedBlocks)->where('type', 'paragraph');
        $this->assertFalse($paragraphBlocks->isEmpty());
    }

    public function test_user_with_permission_can_submit_html_blocks()
    {
        $user = User::factory()->create(['user_type' => 'admin']);
        $user->assignRole('super_admin');
        
        $category = BlogCategory::create(['category_name' => 'Test Category', 'slug' => 'test-cat-2']);

        $blocks = [
            [
                'type' => 'html',
                'data' => ['code' => '<iframe src="https://example.com"></iframe>']
            ]
        ];

        $response = $this->actingAs($user)->post(route('blog.store'), [
            'title' => 'Test Blog 2',
            'slug' => 'test-blog-2',
            'category_id' => $category->id,
            'short_description' => 'Short description',
            'content_blocks' => json_encode($blocks),
            'workflow_action' => 'draft',
        ]);

        $response->assertRedirect(route('blog.index'));

        $blog = Blog::where('slug', 'test-blog-2')->first();
        $this->assertNotNull($blog);

        $savedBlocks = $blog->content_blocks;
        
        $htmlBlocks = collect($savedBlocks)->where('type', 'html');
        $this->assertFalse($htmlBlocks->isEmpty());
        $this->assertEquals('<iframe src="https://example.com"></iframe>', $htmlBlocks->first()['data']['code']);
    }
}
