<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blog_categories')) {
            Schema::create('blog_categories', function (Blueprint $table) {
                $table->id();
                $table->string('category_name');
                $table->string('slug')->unique();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('blogs')) {
            Schema::create('blogs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('title');
                $table->string('slug')->unique();
                $table->string('banner')->nullable();
                $table->text('short_description')->nullable();
                $table->longText('description')->nullable();
                $table->string('meta_title')->nullable();
                $table->string('meta_img')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->tinyInteger('status')->default(1)->index();
                $table->tinyInteger('going_on')->default(0)->index();
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('blogs', function (Blueprint $table) {
                if (!Schema::hasColumn('blogs', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->index()->after('category_id');
                }
                if (!Schema::hasColumn('blogs', 'published_at')) {
                    $table->timestamp('published_at')->nullable()->index()->after('status');
                }
                if (!Schema::hasColumn('blogs', 'going_on')) {
                    $table->tinyInteger('going_on')->default(0)->index()->after('status');
                }
                if (!Schema::hasColumn('blogs', 'meta_keywords')) {
                    $table->text('meta_keywords')->nullable()->after('meta_description');
                }
            });
        }

        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('blog_tag')) {
            Schema::create('blog_tag', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('blog_id')->index();
                $table->unsignedBigInteger('tag_id')->index();
                $table->timestamps();
                $table->unique(['blog_id', 'tag_id']);
            });
        }

        if (!Schema::hasTable('blog_translations')) {
            Schema::create('blog_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('blog_id')->index();
                $table->string('lang', 10)->index();
                $table->string('title');
                $table->text('short_description')->nullable();
                $table->longText('description')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->timestamps();
                $table->unique(['blog_id', 'lang']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_translations');
        Schema::dropIfExists('blog_tag');
        Schema::dropIfExists('tags');
    }
};
