<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_translations')) {
            return;
        }

        Schema::table('product_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('product_translations', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('description');
            }
            if (!Schema::hasColumn('product_translations', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('product_translations', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable()->after('meta_description');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_translations')) {
            return;
        }

        Schema::table('product_translations', function (Blueprint $table) {
            foreach (['meta_keywords', 'meta_description', 'meta_title'] as $column) {
                if (Schema::hasColumn('product_translations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
