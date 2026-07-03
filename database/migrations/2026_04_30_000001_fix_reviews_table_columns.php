<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'type')) {
                $table->string('type')->default('real')->after('viewed');
            }
            if (!Schema::hasColumn('reviews', 'custom_reviewer_name')) {
                $table->string('custom_reviewer_name')->nullable()->after('type');
            }
            if (!Schema::hasColumn('reviews', 'custom_reviewer_image')) {
                $table->string('custom_reviewer_image')->nullable()->after('custom_reviewer_name');
            }
            if (!Schema::hasColumn('reviews', 'photos')) {
                $table->longText('photos')->nullable()->after('custom_reviewer_image');
            }
            if (!Schema::hasColumn('reviews', 'created_at_is_custom')) {
                $table->boolean('created_at_is_custom')->default(false)->after('photos');
            }
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE reviews MODIFY user_id INT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE reviews ALTER COLUMN user_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            foreach (['type', 'custom_reviewer_name', 'custom_reviewer_image', 'photos', 'created_at_is_custom'] as $column) {
                if (Schema::hasColumn('reviews', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
