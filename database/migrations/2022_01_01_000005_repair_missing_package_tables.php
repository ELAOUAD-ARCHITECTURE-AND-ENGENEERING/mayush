<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RepairMissingPackageTables extends Migration
{
    /**
     * Run the migrations.
     * This repair migration ensures that tables defined in legacy SQL updates (v25.sql)
     * are correctly represented in the migration history for SQLite-based testing environments.
     */
    public function up()
    {
        if (!Schema::hasTable('customer_packages')) {
            Schema::create('customer_packages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->nullable();
                $table->double('amount', 28, 2)->nullable();
                $table->integer('product_upload')->nullable();
                $table->string('logo', 150)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customer_products')) {
            Schema::create('customer_products', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 255)->nullable();
                $table->integer('published')->default(0);
                $table->integer('status')->default(0);
                $table->string('added_by', 50)->nullable();
                $table->integer('user_id')->nullable();
                $table->integer('category_id')->nullable();
                $table->integer('subcategory_id')->nullable();
                $table->integer('subsubcategory_id')->nullable();
                $table->integer('brand_id')->nullable();
                $table->string('photos', 255)->nullable();
                $table->string('thumbnail_img', 150)->nullable();
                $table->string('conditon', 50)->nullable();
                $table->text('location')->nullable();
                $table->string('video_provider', 100)->nullable();
                $table->string('video_link', 200)->nullable();
                $table->string('unit', 200)->nullable();
                $table->string('tags', 255)->nullable();
                $table->mediumText('description')->nullable();
                $table->double('unit_price', 28, 2)->default(0.00);
                $table->mediumText('meta_title')->nullable();
                $table->longText('meta_description')->nullable();
                $table->string('meta_img', 150)->nullable();
                $table->string('pdf', 200)->nullable();
                $table->mediumText('slug')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('customer_packages');
        Schema::dropIfExists('customer_products');
    }
}
