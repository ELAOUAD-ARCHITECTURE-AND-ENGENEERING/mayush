<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HardenProductDependencies extends Migration
{
    /**
     * Run the migrations.
     * Ensures ancillary tables required for product creation exist in the test environment.
     */
    public function up()
    {
        if (!Schema::hasTable('warranties')) {
            Schema::create('warranties', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('logo', 100)->nullable();
                $table->string('duration')->nullable();
                $table->string('duration_type')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notes')) {
            Schema::create('notes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->longText('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('uploads')) {
            Schema::create('uploads', function (Blueprint $table) {
                $table->id();
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
        
        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 100);
                $table->string('app_lang_code', 100)->default('en');
                $table->integer('rtl')->default(0);
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('taxes')) {
            Schema::create('taxes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->integer('tax_status')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        //
    }
}
