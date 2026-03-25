<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds FULLTEXT indexes on products.name and products.tags for fast full-text search.
     * Replaces LIKE '%keyword%' scans with MATCH...AGAINST queries.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // We use raw DB statements because Laravel's Blueprint
        // does not expose FULLTEXT for MySQL directly in older versions.
        DB::statement('ALTER TABLE `products` ADD FULLTEXT INDEX `ft_products_name` (`name`)');
        DB::statement('ALTER TABLE `products` ADD FULLTEXT INDEX `ft_products_tags` (`tags`)');
        DB::statement('ALTER TABLE `products` ADD FULLTEXT INDEX `ft_products_name_tags` (`name`, `tags`)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE `products` DROP INDEX `ft_products_name`');
        DB::statement('ALTER TABLE `products` DROP INDEX `ft_products_tags`');
        DB::statement('ALTER TABLE `products` DROP INDEX `ft_products_name_tags`');
    }
};
