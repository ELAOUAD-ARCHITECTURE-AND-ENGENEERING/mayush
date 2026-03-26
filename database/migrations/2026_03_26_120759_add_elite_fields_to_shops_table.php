<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('story_title')->nullable()->after('meta_description');
            $table->longText('story_content')->nullable()->after('story_title');
            $table->unsignedBigInteger('hero_media_id')->nullable()->after('story_content');
            $table->json('social_links')->nullable()->after('hero_media_id');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['story_title', 'story_content', 'hero_media_id', 'social_links']);
        });
    }
};
