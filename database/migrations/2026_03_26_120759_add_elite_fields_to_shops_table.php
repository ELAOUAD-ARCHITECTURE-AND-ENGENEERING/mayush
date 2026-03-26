<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shops', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('shops', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('shops', 'story_title')) {
                $table->string('story_title')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('shops', 'story_content')) {
                $table->longText('story_content')->nullable()->after('story_title');
            }
            if (!Schema::hasColumn('shops', 'hero_media_id')) {
                $table->unsignedBigInteger('hero_media_id')->nullable()->after('story_content');
            }
            if (!Schema::hasColumn('shops', 'social_links')) {
                $table->json('social_links')->nullable()->after('hero_media_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['story_title', 'story_content', 'hero_media_id', 'social_links']);
        });
    }
};
