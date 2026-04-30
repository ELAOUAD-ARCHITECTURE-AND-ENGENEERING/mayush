<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CoreSchemaRepair extends Migration
{
    public function up()
    {
        // 1. Shops Table
        if (!Schema::hasTable('colors')) {
            Schema::create('colors', function (Blueprint $table) {
                $table->id();
                $table->string('name', 30)->nullable();
                $table->string('code', 10)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attributes')) {
            Schema::create('attributes', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attribute_values')) {
            Schema::create('attribute_values', function (Blueprint $table) {
                $table->id();
                $table->integer('attribute_id');
                $table->string('value', 100);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attribute_translations')) {
            Schema::create('attribute_translations', function (Blueprint $table) {
                $table->id();
                $table->integer('attribute_id');
                $table->string('name', 100);
                $table->string('lang', 10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('top_banners')) {
            Schema::create('top_banners', function (Blueprint $table) {
                $table->id();
                $table->tinyInteger('status')->default(1);
                $table->string('link', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('top_banner_translations')) {
            Schema::create('top_banner_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('top_banner_id');
                $table->string('text', 1000)->nullable();
                $table->string('lang', 10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('element_types')) {
            Schema::create('element_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->tinyInteger('is_default')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('element_styles')) {
            Schema::create('element_styles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('element_type_id');
                $table->string('name', 100);
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('custom_alerts')) {
            Schema::create('custom_alerts', function (Blueprint $table) {
                $table->id();
                $table->tinyInteger('status')->default(1);
                $table->string('type', 50)->nullable();
                $table->string('banner', 255)->nullable();
                $table->string('link', 255)->nullable();
                $table->text('description')->nullable();
                $table->string('background_color', 20)->nullable();
                $table->string('text_color', 20)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dynamic_popups')) {
            Schema::create('dynamic_popups', function (Blueprint $table) {
                $table->id();
                $table->tinyInteger('status')->default(1);
                $table->string('title', 255)->nullable();
                $table->text('summary')->nullable();
                $table->string('banner', 255)->nullable();
                $table->string('btn_link', 255)->nullable();
                $table->string('btn_text', 100)->nullable();
                $table->string('btn_text_color', 20)->nullable();
                $table->string('btn_background_color', 20)->nullable();
                $table->string('show_subscribe_form', 10)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('category_translations')) {
            Schema::create('category_translations', function (Blueprint $table) {
                $table->id();
                $table->integer('category_id');
                $table->string('name', 50);
                $table->string('lang', 10);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shops')) {
            Schema::create('shops', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('name', 200)->nullable();
                $table->string('logo', 255)->nullable();
                $table->text('sliders')->nullable();
                $table->string('phone', 20)->nullable();
                $table->string('address', 500)->nullable();
                $table->double('rating', 8, 2)->default(0.00);
                $table->integer('num_of_reviews')->default(0);
                $table->integer('num_of_sale')->default(0);
                $table->integer('verification_status')->default(0);
                $table->text('verification_info')->nullable();
                $table->integer('cash_on_delivery')->default(0);
                $table->integer('admin_to_pay')->default(0);
                $table->string('facebook', 255)->nullable();
                $table->string('instagram', 255)->nullable();
                $table->string('google', 255)->nullable();
                $table->string('twitter', 255)->nullable();
                $table->string('youtube', 255)->nullable();
                $table->string('slug', 255)->nullable();
                $table->string('meta_title', 255)->nullable();
                $table->text('meta_description')->nullable();
                $table->integer('pick_up_point_id')->nullable();
                $table->double('shipping_cost', 20, 2)->default(0.00);
                $table->timestamps();
            });
        }

        // 2. Brands Table
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 50);
                $table->string('logo', 100)->nullable();
                $table->string('slug', 100)->nullable();
                $table->string('meta_title', 255)->nullable();
                $table->text('meta_description')->nullable();
                $table->integer('top')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }

        // 3. Brand Translations
        if (!Schema::hasTable('brand_translations')) {
            Schema::create('brand_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('brand_id');
                $table->string('name', 50);
                $table->string('lang', 10);
                $table->timestamps();
            });
        }

        // 4. Reviews Table
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->integer('user_id')->nullable();
                $table->integer('rating')->default(0);
                $table->mediumText('comment')->nullable();
                $table->integer('status')->default(1);
                $table->integer('viewed')->default(0);
                $table->string('type')->default('real');
                $table->string('custom_reviewer_name')->nullable();
                $table->string('custom_reviewer_image')->nullable();
                $table->text('photos')->nullable();
                $table->boolean('created_at_is_custom')->default(0);
                $table->timestamps();
            });
        }

        // 5. Last Viewed Products
        if (!Schema::hasTable('last_viewed_products')) {
            Schema::create('last_viewed_products', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->integer('user_id');
                $table->timestamps();
            });
        }

        // 6. Product Translations
        if (!Schema::hasTable('product_translations')) {
            Schema::create('product_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->string('name', 200);
                $table->string('unit', 20)->nullable();
                $table->longText('description')->nullable();
                $table->string('lang', 10);
                $table->timestamps();
            });
        }

        // 7. Product Stocks
        if (!Schema::hasTable('product_stocks')) {
            Schema::create('product_stocks', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->string('variant', 255)->nullable();
                $table->string('sku', 255)->nullable();
                $table->double('price', 20, 2)->default(0.00);
                $table->integer('qty')->default(0);
                $table->integer('image')->nullable();
                $table->timestamps();
            });
        }

        // 8. Product Taxes
        if (!Schema::hasTable('product_taxes')) {
            Schema::create('product_taxes', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->double('tax', 20, 2);
                $table->string('tax_type', 10);
                $table->timestamps();
            });
        }

        // 9. Wholesale Prices
        if (!Schema::hasTable('wholesale_prices')) {
            Schema::create('wholesale_prices', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_stock_id');
                $table->integer('min_qty');
                $table->integer('max_qty');
                $table->double('price', 20, 2);
                $table->timestamps();
            });
        }

        // 10. Wishlists
        if (!Schema::hasTable('wishlists')) {
            Schema::create('wishlists', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('product_id');
                $table->timestamps();
            });
        }

        // 11. Flash Deals
        if (!Schema::hasTable('flash_deals')) {
            Schema::create('flash_deals', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 255)->nullable();
                $table->integer('start_date')->nullable();
                $table->integer('end_date')->nullable();
                $table->string('background_color', 255)->nullable();
                $table->string('text_color', 255)->nullable();
                $table->string('banner', 255)->nullable();
                $table->string('slug', 255)->nullable();
                $table->integer('featured')->default(0);
                $table->integer('status')->default(0);
                $table->timestamps();
            });
        }

        // 12. Flash Deal Products
        if (!Schema::hasTable('flash_deal_products')) {
            Schema::create('flash_deal_products', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('flash_deal_id');
                $table->integer('product_id');
                $table->double('discount', 20, 2)->default(0.00);
                $table->string('discount_type', 20)->nullable();
                $table->timestamps();
            });
        }

        // 13. Product Categories Pivot
        if (!Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('product_id');
                $table->integer('category_id');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('colors');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attribute_translations');
        Schema::dropIfExists('top_banners');
        Schema::dropIfExists('top_banner_translations');
        Schema::dropIfExists('element_types');
        Schema::dropIfExists('element_styles');
        Schema::dropIfExists('custom_alerts');
        Schema::dropIfExists('dynamic_popups');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('shops');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('brand_translations');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('last_viewed_products');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('product_taxes');
        Schema::dropIfExists('wholesale_prices');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('flash_deals');
        Schema::dropIfExists('flash_deal_products');
        Schema::dropIfExists('product_categories');
    }
}
