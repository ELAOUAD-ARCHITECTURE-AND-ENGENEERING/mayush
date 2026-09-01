<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::create('inspirations', function (Blueprint $table) use ($isSqlite) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_fr');
            $table->string('title_ar')->nullable();
            $table->string('subtitle_fr')->nullable();
            $table->string('subtitle_ar')->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('hero_image');
            $table->unsignedInteger('hero_image_width')->nullable();
            $table->unsignedInteger('hero_image_height')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_on_home')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            if ($isSqlite) {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::create('inspiration_items', function (Blueprint $table) use ($isSqlite) {
            $table->id();
            $table->unsignedBigInteger('inspiration_id');
            $table->integer('product_id');
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('custom_title_fr')->nullable();
            $table->string('custom_title_ar')->nullable();
            $table->timestamps();

            $table->unique(['inspiration_id', 'product_id'], 'inspiration_item_unique');
            if ($isSqlite) {
                $table->foreign('inspiration_id')->references('id')->on('inspirations')->cascadeOnDelete();
                $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            }
        });

        Schema::create('inspiration_hotspots', function (Blueprint $table) use ($isSqlite) {
            $table->id();
            $table->unsignedBigInteger('inspiration_id');
            $table->unsignedBigInteger('inspiration_item_id');
            $table->decimal('x', 6, 4); // normalized 0.0000–1.0000
            $table->decimal('y', 6, 4);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            if ($isSqlite) {
                $table->unique('inspiration_item_id', 'hotspot_item_unique');
                $table->foreign('inspiration_id')->references('id')->on('inspirations')->cascadeOnDelete();
                $table->foreign('inspiration_item_id')->references('id')->on('inspiration_items')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspiration_hotspots');
        Schema::dropIfExists('inspiration_items');
        Schema::dropIfExists('inspirations');
    }
};
