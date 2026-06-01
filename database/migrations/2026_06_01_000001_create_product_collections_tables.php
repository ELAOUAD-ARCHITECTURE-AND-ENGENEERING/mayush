<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('mode')->default('hybrid');
            $table->json('category_ids')->nullable();
            $table->json('brand_ids')->nullable();
            $table->json('seller_ids')->nullable();
            $table->string('tags')->nullable();
            $table->decimal('min_price', 20, 2)->nullable();
            $table->decimal('max_price', 20, 2)->nullable();
            $table->string('default_sort')->default('newest');
            $table->unsignedBigInteger('hero_image')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->unsignedBigInteger('meta_image')->nullable();
            $table->boolean('show_best_selling')->default(true);
            $table->boolean('show_recently_viewed')->default(true);
            $table->boolean('status')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_collection_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_collection_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_collection_id', 'product_id'], 'product_collection_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_collection_product');
        Schema::dropIfExists('product_collections');
    }
};
