<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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
                $table->text('photos')->nullable()->after('custom_reviewer_image');
            }
            if (!Schema::hasColumn('reviews', 'created_at_is_custom')) {
                $table->boolean('created_at_is_custom')->default(0)->after('photos');
            }
            
            // Note: user_id becomes nullable to support guest/system reviews
            $table->integer('user_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['type', 'custom_reviewer_name', 'custom_reviewer_image', 'photos', 'created_at_is_custom']);
            $table->integer('user_id')->nullable(false)->change();
        });
    }
};
