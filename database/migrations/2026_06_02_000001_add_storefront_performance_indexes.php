<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('products', ['published', 'approved', 'auction_product', 'todays_deal', 'id'], 'products_storefront_deals_idx');
        $this->addIndex('products', ['published', 'approved', 'auction_product', 'featured', 'id'], 'products_storefront_featured_idx');
        $this->addIndex('products', ['published', 'approved', 'category_id', 'created_at'], 'products_storefront_category_idx');
        $this->addIndex('categories', ['featured', 'order_level'], 'categories_storefront_featured_idx');
        $this->addIndex('categories', ['hot_category', 'order_level'], 'categories_storefront_hot_idx');
        $this->addIndex('blogs', ['status', 'published_at', 'created_at'], 'blogs_storefront_published_idx');
        $this->addIndex('carts', ['temp_user_id', 'status'], 'carts_storefront_guest_idx');
        $this->addIndex('carts', ['user_id', 'status'], 'carts_storefront_user_idx');
        $this->addIndex('order_details', ['seller_id', 'created_at'], 'order_details_storefront_seller_idx');
    }

    public function down(): void
    {
        $this->dropIndex('products', 'products_storefront_deals_idx');
        $this->dropIndex('products', 'products_storefront_featured_idx');
        $this->dropIndex('products', 'products_storefront_category_idx');
        $this->dropIndex('categories', 'categories_storefront_featured_idx');
        $this->dropIndex('categories', 'categories_storefront_hot_idx');
        $this->dropIndex('blogs', 'blogs_storefront_published_idx');
        $this->dropIndex('carts', 'carts_storefront_guest_idx');
        $this->dropIndex('carts', 'carts_storefront_user_idx');
        $this->dropIndex('order_details', 'order_details_storefront_seller_idx');
    }

    /**
     * @param  array<string>  $columns
     */
    private function addIndex(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table)
            || $this->hasIndex($table, $name)
            || collect($columns)->contains(fn ($column) => ! Schema::hasColumn($table, $column))) {
            return;
        }

        Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
    }

    private function dropIndex(string $table, string $name): void
    {
        if (Schema::hasTable($table) && $this->hasIndex($table, $name)) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
        }
    }

    private function hasIndex(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index) => ($index['name'] ?? null) === $name);
    }
};
