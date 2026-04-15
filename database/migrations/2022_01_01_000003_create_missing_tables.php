<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMissingTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('business_settings')) {
            Schema::create('business_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->longText('value')->nullable();
                $table->string('lang', 30)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attributes')) {
            Schema::create('attributes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('attribute_category')) {
            Schema::create('attribute_category', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id');
                $table->integer('attribute_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('uploads')) {
            Schema::create('uploads', function (Blueprint $table) {
                $table->increments('id');
                $table->string('file_original_name')->nullable();
                $table->string('file_name')->nullable();
                $table->integer('user_id')->nullable();
                $table->string('extension', 10)->nullable();
                $table->string('type', 15)->nullable();
                $table->integer('file_size')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->integer('permission_id');
                $table->string('model_type');
                $table->integer('model_id');
                $table->primary(['permission_id', 'model_id', 'model_type']);
            });
        }
        
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->integer('role_id');
                $table->string('model_type');
                $table->integer('model_id');
                $table->primary(['role_id', 'model_id', 'model_type']);
            });
        }

        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->integer('permission_id');
                $table->integer('role_id');
                $table->primary(['permission_id', 'role_id']);
            });
        }

        if (!Schema::hasTable('translations')) {
            Schema::create('translations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('lang', 10)->nullable();
                $table->text('lang_key')->nullable();
                $table->text('lang_value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('app_translations')) {
            Schema::create('app_translations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('lang', 10)->nullable();
                $table->string('lang_key')->nullable();
                $table->string('lang_value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('addons')) {
            Schema::create('addons', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('unique_identifier')->nullable();
                $table->string('version')->nullable();
                $table->integer('activated')->default(1);
                $table->string('image', 1000)->nullable();
                $table->string('purchase_code')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 200);
                $table->string('added_by', 6)->default('admin');
                $table->integer('user_id');
                $table->integer('category_id');
                $table->integer('brand_id')->nullable();
                $table->string('photos', 2000)->nullable();
                $table->string('thumbnail_img', 100)->nullable();
                $table->string('video_provider', 20)->nullable();
                $table->string('video_link', 100)->nullable();
                $table->string('tags', 500)->nullable();
                $table->longText('description')->nullable();
                $table->double('unit_price', 20, 2);
                $table->double('purchase_price', 20, 2)->nullable();
                $table->integer('variant_product')->default(0);
                $table->string('attributes', 1000)->default('[]');
                $table->mediumText('choice_options')->nullable();
                $table->mediumText('colors')->nullable();
                $table->text('variations')->nullable();
                $table->integer('todays_deal')->default(0);
                $table->integer('published')->default(1);
                $table->tinyInteger('approved')->default(1);
                $table->string('stock_visibility_state', 10)->default('quantity');
                $table->tinyInteger('cash_on_delivery')->default(0);
                $table->integer('featured')->default(0);
                $table->integer('seller_featured')->default(0);
                $table->integer('current_stock')->default(0);
                $table->string('unit', 20)->nullable();
                $table->double('weight', 8, 2)->default(0.00);
                $table->integer('min_qty')->default(1);
                $table->integer('low_stock_quantity')->nullable();
                $table->double('discount', 20, 2)->nullable();
                $table->string('discount_type', 10)->nullable();
                $table->integer('discount_start_date')->nullable();
                $table->integer('discount_end_date')->nullable();
                $table->double('tax', 20, 2)->nullable();
                $table->string('tax_type', 10)->nullable();
                $table->string('shipping_type', 20)->default('flat_rate');
                $table->double('shipping_cost', 20, 2)->default(0.00);
                $table->tinyInteger('is_quantity_multiplied')->default(0);
                $table->integer('est_shipping_days')->nullable();
                $table->integer('num_of_sale')->default(0);
                $table->mediumText('meta_title')->nullable();
                $table->longText('meta_description')->nullable();
                $table->string('meta_img', 255)->nullable();
                $table->string('pdf', 255)->nullable();
                $table->mediumText('slug');
                $table->double('rating', 8, 2)->default(0.00);
                $table->string('barcode', 255)->nullable();
                $table->integer('digital')->default(0);
                $table->integer('auction_product')->default(0);
                $table->string('file_name', 255)->nullable();
                $table->string('file_path', 255)->nullable();
                $table->timestamps();
            });
        }


        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('code', 100);
                $table->string('app_lang_code', 255)->default('en');
                $table->integer('rtl')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('business_settings');
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('attribute_category');
        Schema::dropIfExists('uploads');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('app_translations');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('products');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('category_translations');
    }
}
