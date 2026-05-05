<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('blog_categories') || Schema::hasColumn('blog_categories', 'status')) {
            return;
        }

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->after('slug')->index();
        });
    }

    public function down(): void
    {
        // Non-destructive rollback: category visibility is production content state.
    }
};
