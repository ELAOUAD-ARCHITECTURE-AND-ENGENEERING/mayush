<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blogs')) {
            Schema::table('blogs', function (Blueprint $table) {
                if (!Schema::hasColumn('blogs', 'workflow_status')) {
                    $table->string('workflow_status', 30)->default('published')->index()->after('status');
                }

                if (!Schema::hasColumn('blogs', 'content_blocks')) {
                    $table->longText('content_blocks')->nullable()->after('description');
                }

                if (!Schema::hasColumn('blogs', 'submitted_at')) {
                    $table->timestamp('submitted_at')->nullable()->after('published_at');
                }

                if (!Schema::hasColumn('blogs', 'reviewed_by')) {
                    $table->unsignedBigInteger('reviewed_by')->nullable()->index()->after('submitted_at');
                }

                if (!Schema::hasColumn('blogs', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
                }

                if (!Schema::hasColumn('blogs', 'review_note')) {
                    $table->text('review_note')->nullable()->after('reviewed_at');
                }
            });

            DB::table('blogs')
                ->whereNull('workflow_status')
                ->orWhere('workflow_status', '')
                ->update([
                    'workflow_status' => DB::raw("CASE WHEN status = 1 THEN 'published' ELSE 'draft' END"),
                ]);
        }

        if (!Schema::hasTable('blog_versions')) {
            Schema::create('blog_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
                $table->unsignedBigInteger('actor_id')->nullable()->index();
                $table->unsignedInteger('version_number');
                $table->string('action', 40)->default('saved');
                $table->longText('snapshot');
                $table->timestamps();

                $table->unique(['blog_id', 'version_number']);
                $table->index(['blog_id', 'created_at']);
            });
        }

        $this->seedBlogRolesAndPermissions();
    }

    public function down(): void
    {
        if (Schema::hasTable('blog_versions')) {
            Schema::dropIfExists('blog_versions');
        }

        if (Schema::hasTable('blogs')) {
            Schema::table('blogs', function (Blueprint $table) {
                foreach (['review_note', 'reviewed_at', 'reviewed_by', 'submitted_at', 'content_blocks', 'workflow_status'] as $column) {
                    if (Schema::hasColumn('blogs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedBlogRolesAndPermissions(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $now = now();
        $permissions = [
            'blog_super_admin',
            'manage_blog_authors',
            'view_blogs',
            'add_blog',
            'edit_blog',
            'delete_blog',
            'publish_blog',
            'review_blog',
            'view_own_blogs',
            'add_own_blog',
            'edit_own_blog',
            'delete_own_blog',
            'submit_blog_for_review',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        foreach (['blog_super_admin', 'author'] as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role, 'guard_name' => 'web'],
                ['updated_at' => $now, 'created_at' => $now]
            );
        }

        $blogSuperRoleIds = DB::table('roles')
            ->whereIn('name', ['blog_super_admin', 'Super Admin', 'Admin'])
            ->pluck('id');

        $blogPermissionIds = DB::table('permissions')
            ->whereIn('name', $permissions)
            ->pluck('id');

        foreach ($blogSuperRoleIds as $roleId) {
            foreach ($blogPermissionIds as $permissionId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        $authorRoleId = DB::table('roles')->where('name', 'author')->value('id');
        $authorPermissionIds = DB::table('permissions')
            ->whereIn('name', ['view_own_blogs', 'add_own_blog', 'edit_own_blog', 'delete_own_blog', 'submit_blog_for_review'])
            ->pluck('id');

        foreach ($authorPermissionIds as $permissionId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $authorRoleId,
            ]);
        }

        $blogSuperRoleId = DB::table('roles')->where('name', 'blog_super_admin')->value('id');
        $adminIds = User::query()->where('user_type', 'admin')->pluck('id');

        foreach ($adminIds as $adminId) {
            DB::table('model_has_roles')->updateOrInsert([
                'role_id' => $blogSuperRoleId,
                'model_type' => User::class,
                'model_id' => $adminId,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
