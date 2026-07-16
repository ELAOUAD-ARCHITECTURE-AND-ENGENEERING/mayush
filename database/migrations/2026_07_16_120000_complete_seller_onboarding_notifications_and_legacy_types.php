<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->widenSellerDocumentType();
        $this->seedNotificationTypes();
        $this->seedEmailTemplates();
    }

    private function widenSellerDocumentType(): void
    {
        if (!Schema::hasTable('seller_documents') || !Schema::hasColumn('seller_documents', 'document_type')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `seller_documents` MODIFY `document_type` VARCHAR(100) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE seller_documents ALTER COLUMN document_type TYPE VARCHAR(100)');
        }
        // SQLite already represents Laravel enum columns as unconstrained text.
    }

    private function seedNotificationTypes(): void
    {
        if (!Schema::hasTable('notification_types')) {
            return;
        }

        $types = [
            ['type' => 'shop_verify_request_submitted', 'name' => 'Seller Documents Submitted', 'default_text' => 'A seller submitted onboarding documents for review.', 'user_type' => 'admin'],
            ['type' => 'shop_verify_request_approved', 'name' => 'Seller Application Approved', 'default_text' => 'Your seller application has been approved.', 'user_type' => 'seller'],
            ['type' => 'shop_verify_request_rejected', 'name' => 'Seller Application Rejected', 'default_text' => 'Your seller application requires correction.', 'user_type' => 'seller'],
            ['type' => 'seller_onboarding_registration_incomplete', 'name' => 'Seller Registration Completed', 'default_text' => 'Registration is complete. Submit your onboarding documents to activate your seller account.', 'user_type' => 'seller'],
            ['type' => 'seller_onboarding_documents_submitted', 'name' => 'Seller Documents Submitted', 'default_text' => 'Your onboarding documents were submitted and are awaiting administrator review.', 'user_type' => 'seller'],
            ['type' => 'seller_onboarding_correction_required', 'name' => 'Seller Document Correction Required', 'default_text' => 'A correction is required for one or more seller onboarding documents.', 'user_type' => 'seller'],
            ['type' => 'seller_onboarding_suspended', 'name' => 'Seller Account Suspended', 'default_text' => 'Your seller account has been suspended. Seller operations are currently restricted.', 'user_type' => 'seller'],
            ['type' => 'seller_onboarding_reactivated', 'name' => 'Seller Account Reactivated', 'default_text' => 'Your seller account has been reactivated.', 'user_type' => 'seller'],
        ];

        foreach ($types as $type) {
            if (DB::table('notification_types')->where('type', $type['type'])->exists()) {
                continue;
            }

            $payload = array_merge($type, [
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasColumn('notification_types', 'user_type')) {
                $payload['user_type'] = $type['user_type'];
            } else {
                unset($payload['user_type']);
            }

            DB::table('notification_types')->insert($payload);
        }
    }

    private function seedEmailTemplates(): void
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        $templates = [
            [
                'identifier' => 'seller_documents_submitted',
                'subject' => 'Your seller documents are under review on [[store_name]]',
                'default_text' => '<p>Dear [[seller_name]],</p><p>Your onboarding documents for <strong>[[seller_shop_name]]</strong> were submitted successfully and are now under review.</p><p>Current status: <strong>[[approval_status]]</strong>.</p><p>You can check progress from your <a href="[[onboarding_url]]">seller onboarding page</a>.</p><p>Regards,<br>[[store_name]] Team</p>',
            ],
            [
                'identifier' => 'seller_document_correction_required',
                'subject' => 'Correction required for your seller application on [[store_name]]',
                'default_text' => '<p>Dear [[seller_name]],</p><p>A correction is required for <strong>[[document_type]]</strong> (version [[document_version]]) for [[seller_shop_name]].</p><blockquote>[[reason]]</blockquote><p>Please upload a corrected file from your <a href="[[onboarding_url]]">seller onboarding page</a>.</p><p>Regards,<br>[[store_name]] Team</p>',
            ],
            [
                'identifier' => 'seller_account_suspended',
                'subject' => 'Your seller account on [[store_name]] has been suspended',
                'default_text' => '<p>Dear [[seller_name]],</p><p>Your seller account for <strong>[[seller_shop_name]]</strong> has been suspended. Seller operations and public visibility are currently restricted.</p><p>Please contact [[admin_email]] if you need assistance.</p>',
            ],
            [
                'identifier' => 'seller_account_reactivated',
                'subject' => 'Your seller account on [[store_name]] has been reactivated',
                'default_text' => '<p>Dear [[seller_name]],</p><p>Your seller account for <strong>[[seller_shop_name]]</strong> has been reactivated.</p><p>You can access your <a href="[[dashboard_url]]">seller dashboard</a>.</p><p>Regards,<br>[[store_name]] Team</p>',
            ],
        ];

        foreach ($templates as $template) {
            if (DB::table('email_templates')->where('identifier', $template['identifier'])->exists()) {
                continue;
            }

            $payload = [
                'identifier' => $template['identifier'],
                'subject' => $template['subject'],
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('email_templates', 'default_text')) {
                $payload['default_text'] = $template['default_text'];
            } elseif (Schema::hasColumn('email_templates', 'content')) {
                $payload['content'] = $template['default_text'];
            }

            if (Schema::hasColumn('email_templates', 'receiver')) {
                $payload['receiver'] = 'seller';
            }

            DB::table('email_templates')->insert($payload);
        }
    }

    public function down(): void
    {
        // Compatibility data and the widened document type are intentionally retained.
        // Narrowing the column could destroy migrated supporting-document metadata.
    }
};
