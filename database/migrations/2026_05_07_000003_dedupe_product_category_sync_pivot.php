<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'product_categories_product_category_unique';

    public function up(): void
    {
        if (!Schema::hasTable('product_categories')) {
            return;
        }

        // 1. Ensure 'id' column exists. In some environments, this pivot table 
        // might have been created without an auto-incrementing ID.
        if (!Schema::hasColumn('product_categories', 'id')) {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->bigIncrements('id')->first();
            });
        }

        // 2. Deduplicate using the ID column
        DB::table('product_categories')
            ->select('product_id', 'category_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('product_id', 'category_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function ($duplicate) {
                DB::table('product_categories')
                    ->where('product_id', $duplicate->product_id)
                    ->where('category_id', $duplicate->category_id)
                    ->where('id', '!=', $duplicate->keep_id)
                    ->delete();
            });

        try {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->unique(['product_id', 'category_id'], self::INDEX_NAME);
            });
        } catch (Throwable $exception) {
            if (!str_contains($exception->getMessage(), self::INDEX_NAME)
                && !str_contains(strtolower($exception->getMessage()), 'already exists')) {
                throw $exception;
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_categories')) {
            return;
        }

        try {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->dropUnique(self::INDEX_NAME);
            });
        } catch (Throwable $exception) {
            if (!str_contains($exception->getMessage(), self::INDEX_NAME)
                && !str_contains(strtolower($exception->getMessage()), 'no such index')) {
                throw $exception;
            }
        }
    }
};
