<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('frequently_bought_products')) {
            Schema::create('frequently_bought_products', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id')->index();
                $table->integer('frequently_bought_product_id')->index();
                $table->integer('category_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id')->index();
                $table->integer('user_id')->nullable()->index();
                $table->string('type')->default('real'); // real, custom
                $table->string('custom_reviewer_name')->nullable();
                $table->string('custom_reviewer_image')->nullable();
                $table->integer('rating')->default(0);
                $table->mediumText('comment')->nullable();
                $table->longText('photos')->nullable();
                $table->integer('viewed')->default(0);
                $table->integer('status')->default(1);
                $table->boolean('created_at_is_custom')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_translations')) {
            Schema::create('product_translations', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id')->index();
                $table->string('name', 200);
                $table->longText('description')->nullable();
                $table->string('lang', 10)->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_stocks')) {
            Schema::create('product_stocks', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id')->index();
                $table->string('variant')->nullable();
                $table->string('sku')->nullable()->index();
                $table->double('price', 20, 2)->default(0);
                $table->integer('qty')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50);
                $table->string('logo', 100)->nullable();
                $table->string('meta_title')->nullable();
                $table->longText('meta_description')->nullable();
                $table->string('slug')->nullable()->index();
                $table->integer('featured')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_taxes')) {
            Schema::create('product_taxes', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->integer('tax_id')->nullable();
                $table->double('tax', 20, 2);
                $table->string('tax_type', 10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('wishlists')) {
             Schema::create('wishlists', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->integer('product_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frequently_bought_products');
    }
};
