<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The product_collections table was created with unsignedBigInteger columns
     * for hero_image and meta_image, but uploads.id uses increments() which is
     * unsignedInteger. Align the column types so FK constraints can be added later.
     */
    public function up(): void
    {
        Schema::table('product_collections', function (Blueprint $table) {
            $table->unsignedInteger('hero_image')->nullable()->change();
            $table->unsignedInteger('meta_image')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_collections', function (Blueprint $table) {
            $table->unsignedBigInteger('hero_image')->nullable()->change();
            $table->unsignedBigInteger('meta_image')->nullable()->change();
        });
    }
};
