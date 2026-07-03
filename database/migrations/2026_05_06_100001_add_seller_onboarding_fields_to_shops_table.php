<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellerOnboardingFieldsToShopsTable extends Migration
{
    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shops', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'under_review', 'approved', 'rejected'])
                      ->default('pending')
                      ->after('registration_approval');
            }
            if (!Schema::hasColumn('shops', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('shops', 'documents_submitted_at')) {
                $table->timestamp('documents_submitted_at')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('shops', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('documents_submitted_at');
            }
            if (!Schema::hasColumn('shops', 'reviewed_by')) {
                $table->integer('reviewed_by')->nullable()->after('reviewed_at');
            }
            if (!Schema::hasColumn('shops', 'resubmission_count')) {
                $table->tinyInteger('resubmission_count')->default(0)->after('reviewed_by');
            }
        });

        // Backfill existing approved shops so new column is consistent
        DB::table('shops')->where('registration_approval', 1)->update(['approval_status' => 'approved']);
        DB::table('shops')->where('registration_approval', 0)->update(['approval_status' => 'pending']);
    }

    public function down()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'approval_status', 'rejection_reason',
                'documents_submitted_at', 'reviewed_at',
                'reviewed_by', 'resubmission_count',
            ]);
        });
    }
}
