<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Hardens the shops table by adding columns present in the project but missing from baseline migrations.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shops', 'rc')) {
                $table->string('rc')->nullable()->after('name');
            }
            if (!Schema::hasColumn('shops', 'type')) {
                $table->string('type')->nullable()->after('rc');
            }
            if (!Schema::hasColumn('shops', 'registration_approval')) {
                $table->integer('registration_approval')->default(1)->after('package_invalid_at');
            }
            if (!Schema::hasColumn('shops', 'business_info')) {
                $table->text('business_info')->nullable();
            }
            if (!Schema::hasColumn('shops', 'gst_verification')) {
                $table->integer('gst_verification')->default(0);
            }
            if (!Schema::hasColumn('shops', 'cash_on_delivery_status')) {
                $table->integer('cash_on_delivery_status')->default(0);
            }
            if (!Schema::hasColumn('shops', 'commission_percentage')) {
                $table->double('commission_percentage', 8, 2)->default(0.00);
            }
            if (!Schema::hasColumn('shops', 'delivery_pickup_latitude')) {
                $table->float('delivery_pickup_latitude', 17, 15)->nullable();
            }
            if (!Schema::hasColumn('shops', 'delivery_pickup_longitude')) {
                $table->float('delivery_pickup_longitude', 17, 15)->nullable();
            }
            if (!Schema::hasColumn('shops', 'top_banner_image')) {
                $table->string('top_banner_image')->nullable();
            }
            if (!Schema::hasColumn('shops', 'top_banner_link')) {
                $table->string('top_banner_link')->nullable();
            }
            if (!Schema::hasColumn('shops', 'slider_images')) {
                $table->text('slider_images')->nullable();
            }
            if (!Schema::hasColumn('shops', 'slider_links')) {
                $table->text('slider_links')->nullable();
            }
            if (!Schema::hasColumn('shops', 'banner_full_width_1_images')) {
                $table->text('banner_full_width_1_images')->nullable();
            }
            if (!Schema::hasColumn('shops', 'banner_full_width_1_links')) {
                $table->text('banner_full_width_1_links')->nullable();
            }
            if (!Schema::hasColumn('shops', 'banners_half_width_images')) {
                $table->text('banners_half_width_images')->nullable();
            }
            if (!Schema::hasColumn('shops', 'banners_half_width_links')) {
                $table->text('banners_half_width_links')->nullable();
            }
            if (!Schema::hasColumn('shops', 'banner_full_width_2_images')) {
                $table->text('banner_full_width_2_images')->nullable();
            }
            if (!Schema::hasColumn('shops', 'banner_full_width_2_links')) {
                $table->text('banner_full_width_2_links')->nullable();
            }
            if (!Schema::hasColumn('shops', 'custom_followers')) {
                $table->integer('custom_followers')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Usually we don't drop these as they are "ghost" columns we are just formalizing
        });
    }
};
