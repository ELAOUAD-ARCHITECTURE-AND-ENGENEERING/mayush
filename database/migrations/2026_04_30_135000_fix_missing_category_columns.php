<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'hot_category')) {
                    $table->tinyInteger('hot_category')->default(0)->after('featured');
                }
                if (!Schema::hasColumn('categories', 'meta_keywords')) {
                    $table->text('meta_keywords')->nullable()->after('meta_description');
                }
            });
        }

        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'meta_keywords')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('meta_keywords')->nullable()->after('meta_description');
            });
        }

        if (Schema::hasTable('brands') && !Schema::hasColumn('brands', 'meta_keywords')) {
            Schema::table('brands', function (Blueprint $table) {
                $table->text('meta_keywords')->nullable()->after('meta_description');
            });
        }
    }

    public function down(): void
    {
        foreach ([
            'categories' => ['hot_category'],
            'products' => ['meta_keywords'],
            'brands' => ['meta_keywords'],
        ] as $tableName => $columns) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
