<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedSellerOnboardingEmailTemplates extends Migration
{
    protected array $templates = [
        [
            'identifier'    => 'seller_onboarding_documents_request',
            'subject'       => 'Action Required: Submit Your Documents to Complete Registration on [[store_name]]',
            'default_text'  => '<p>Dear [[seller_name]],</p>
<p>Thank you for registering as a seller on <strong>[[store_name]]</strong>!</p>
<p>To complete your application and get your shop approved, please submit the following mandatory documents through your seller dashboard:</p>
<ul>
  <li>✅ Signed MayushSeller Contract (download available in your dashboard)</li>
  <li>✅ Government-Issued Photo ID</li>
  <li>✅ Business Registration Documents</li>
  <li>📎 Any additional professional certifications relevant to your business category (optional)</li>
</ul>
<p>Please log in to your seller dashboard and navigate to <strong>Account Verification → Document Upload</strong> to submit your documents.</p>
<p>Your shop will remain in <em>Pending Approval</em> status until your documents are reviewed by our team (typically within 48 hours).</p>
<p>If you have any questions, please contact us at [[admin_email]].</p>
<p>Best regards,<br>[[store_name]] Team</p>',
            'status'        => 1,
            'receiver'      => 'seller',
        ],
        [
            'identifier'    => 'seller_documents_received_admin',
            'subject'       => 'New Seller Documents Submitted: [[seller_shop_name]]',
            'default_text'  => '<p>Dear [[admin_name]],</p>
<p>A seller has submitted their onboarding documents for review:</p>
<ul>
  <li><strong>Seller Name:</strong> [[seller_name]]</li>
  <li><strong>Shop Name:</strong> [[seller_shop_name]]</li>
  <li><strong>Email:</strong> [[seller_email]]</li>
  <li><strong>Submitted At:</strong> [[date]]</li>
  <li><strong>Resubmission Count:</strong> [[resubmission_count]]</li>
</ul>
<p>Please log in to the admin panel to review the documents and approve or reject the application.</p>
<p>Admin Panel: [[admin_panel_url]]</p>
<p>Regards,<br>[[store_name]] System</p>',
            'status'        => 1,
            'receiver'      => 'admin',
        ],
        [
            'identifier'    => 'seller_application_approved',
            'subject'       => '🎉 Congratulations! Your Seller Account on [[store_name]] is Approved',
            'default_text'  => '<p>Dear [[seller_name]],</p>
<p>We are thrilled to let you know that your seller application for <strong>[[seller_shop_name]]</strong> on <strong>[[store_name]]</strong> has been <strong>approved</strong>!</p>
<p>You can now log in to your seller dashboard and start listing your products:</p>
<p><a href="[[login_url]]" style="background:#28a745;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none;">Go to Seller Dashboard</a></p>
<p>Welcome to the [[store_name]] family. We look forward to a successful partnership!</p>
<p>If you need any help getting started, feel free to contact us at [[admin_email]].</p>
<p>Best regards,<br>[[store_name]] Team</p>',
            'status'        => 1,
            'receiver'      => 'seller',
        ],
        [
            'identifier'    => 'seller_application_rejected',
            'subject'       => 'Update on Your Seller Application for [[store_name]]',
            'default_text'  => '<p>Dear [[seller_name]],</p>
<p>Thank you for your interest in selling on <strong>[[store_name]]</strong>.</p>
<p>After reviewing your submitted documents for <strong>[[seller_shop_name]]</strong>, we were unable to approve your application at this time for the following reason:</p>
<blockquote style="border-left:4px solid #dc3545;padding-left:15px;color:#555;">[[rejection_reason]]</blockquote>
<p>You may resubmit your corrected documents through your seller dashboard. Please note that you have <strong>[[resubmission_attempts_remaining]] resubmission attempt(s)</strong> remaining.</p>
<p><a href="[[login_url]]" style="background:#dc3545;color:#fff;padding:10px 20px;border-radius:5px;text-decoration:none;">Resubmit Documents</a></p>
<p>If you believe this was an error or need clarification, please contact us at [[admin_email]].</p>
<p>Regards,<br>[[store_name]] Team</p>',
            'status'        => 1,
            'receiver'      => 'seller',
        ],
    ];

    public function up()
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        foreach ($this->templates as $template) {
            $exists = DB::table('email_templates')
                ->where('identifier', $template['identifier'])
                ->exists();

            if (!$exists) {
                DB::table('email_templates')->insert(array_merge($this->payloadForSchema($template), [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        $identifiers = array_column($this->templates, 'identifier');
        DB::table('email_templates')->whereIn('identifier', $identifiers)->delete();
    }

    private function payloadForSchema(array $template): array
    {
        $payload = [
            'identifier' => $template['identifier'],
            'subject' => $template['subject'],
            'status' => $template['status'],
        ];

        if (Schema::hasColumn('email_templates', 'default_text')) {
            $payload['default_text'] = $template['default_text'];
        } elseif (Schema::hasColumn('email_templates', 'content')) {
            $payload['content'] = $template['default_text'];
        }

        if (Schema::hasColumn('email_templates', 'receiver')) {
            $payload['receiver'] = $template['receiver'];
        }

        return $payload;
    }
}
