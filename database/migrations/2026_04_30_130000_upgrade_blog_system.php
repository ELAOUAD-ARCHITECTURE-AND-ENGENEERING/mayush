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
                $table->string('hero_image')->nullable();
                $table->string('badge_type')->nullable()->index();
                $table->string('custom_badge_text')->nullable();
                $table->unsignedSmallInteger('read_time_minutes')->nullable();
                $table->boolean('is_featured')->default(false)->index();
                $table->string('canonical_url')->nullable();
                $table->boolean('schema_enabled')->default(true);
                $table->unsignedBigInteger('shop_id')->nullable()->index();
                $table->text('vendor_quote')->nullable();
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
                if (!Schema::hasColumn('blogs', 'hero_image')) {
                    $table->string('hero_image')->nullable()->after('banner');
                }
                if (!Schema::hasColumn('blogs', 'badge_type')) {
                    $table->string('badge_type')->nullable()->index()->after('hero_image');
                }
                if (!Schema::hasColumn('blogs', 'custom_badge_text')) {
                    $table->string('custom_badge_text')->nullable()->after('badge_type');
                }
                if (!Schema::hasColumn('blogs', 'read_time_minutes')) {
                    $table->unsignedSmallInteger('read_time_minutes')->nullable()->after('description');
                }
                if (!Schema::hasColumn('blogs', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->index()->after('published_at');
                }
                if (!Schema::hasColumn('blogs', 'canonical_url')) {
                    $table->string('canonical_url')->nullable()->after('meta_img');
                }
                if (!Schema::hasColumn('blogs', 'schema_enabled')) {
                    $table->boolean('schema_enabled')->default(true)->after('canonical_url');
                }
                if (!Schema::hasColumn('blogs', 'shop_id')) {
                    $table->unsignedBigInteger('shop_id')->nullable()->index()->after('user_id');
                }
                if (!Schema::hasColumn('blogs', 'vendor_quote')) {
                    $table->text('vendor_quote')->nullable()->after('shop_id');
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

        if (!Schema::hasTable('blog_product')) {
            Schema::create('blog_product', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('blog_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->string('placement')->default('manual')->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['blog_id', 'product_id', 'placement'], 'blog_product_unique_placement');
            });
        }

        if (!Schema::hasTable('blog_subscriber_logs')) {
            Schema::create('blog_subscriber_logs', function (Blueprint $table) {
                $table->id();
                $table->string('email')->index();
                $table->string('placement')->index();
                $table->unsignedBigInteger('blog_id')->nullable()->index();
                $table->string('blog_title')->nullable();
                $table->string('provider')->default('local');
                $table->string('provider_status')->nullable();
                $table->longText('provider_response')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('subscribed_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_translations');
        Schema::dropIfExists('blog_tag');
        Schema::dropIfExists('blog_subscriber_logs');
        Schema::dropIfExists('blog_product');
        Schema::dropIfExists('tags');
    }
};
