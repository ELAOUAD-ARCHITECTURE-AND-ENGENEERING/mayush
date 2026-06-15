<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('club_points', function (Blueprint $table) {
            if (!Schema::hasColumn('club_points', 'convert_status')) {
                $table->integer('convert_status')->default(0)->after('points');
            }
        });

        Schema::table('club_point_details', function (Blueprint $table) {
            if (!Schema::hasColumn('club_point_details', 'user_id')) {
                $table->bigInteger('user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('club_point_details', 'refunded')) {
                $table->integer('refunded')->default(0)->after('points');
            }
            if (!Schema::hasColumn('club_point_details', 'converted_amount')) {
                $table->double('converted_amount', 20, 2)->default(0)->after('refunded');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_points', function (Blueprint $table) {
            $table->dropColumn('convert_status');
        });

        Schema::table('club_point_details', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'refunded', 'converted_amount']);
        });
    }
};
