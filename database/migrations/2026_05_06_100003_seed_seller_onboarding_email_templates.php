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
            'default_text'  => '<div style="font-family: \'Public Sans\', \'Inter\', Helvetica, Arial, sans-serif; color: #1f2937; line-height: 1.6; font-size: 15px;">
    <h2 style="font-size: 20px; font-weight: 600; color: #111827; margin-bottom: 20px; margin-top: 0;">Welcome to [[store_name]], [[seller_name]]!</h2>
    <p style="margin-bottom: 20px; color: #4b5563;">Thank you for taking the first step in registering as a seller on our platform. We are thrilled to have you!</p>
    <p style="margin-bottom: 15px; color: #4b5563;">To complete your application and activate your shop, please provide the following mandatory documents through your seller dashboard:</p>
    <div style="background-color: #f3f4f6; border-left: 4px solid #0b60bd; padding: 20px; margin-bottom: 25px; border-radius: 4px;">
        <ul style="margin: 0; padding-left: 0; list-style-type: none;">
            <li style="margin-bottom: 12px;"><span style="color: #0b60bd; margin-right: 8px; font-size: 14px;">✔</span> <strong>Signed MayushSeller Contract</strong> (downloadable from your dashboard)</li>
            <li style="margin-bottom: 12px;"><span style="color: #0b60bd; margin-right: 8px; font-size: 14px;">✔</span> <strong>Government-Issued Photo ID</strong></li>
            <li style="margin-bottom: 12px;"><span style="color: #0b60bd; margin-right: 8px; font-size: 14px;">✔</span> <strong>Business Registration Documents</strong></li>
            <li><span style="color: #6b7280; margin-right: 8px; font-size: 14px;">📎</span> <span style="color: #6b7280;">Any additional professional certifications relevant to your category (optional)</span></li>
        </ul>
    </div>
    <p style="margin-bottom: 20px; color: #4b5563;">To submit your documents, please log in to your dashboard and navigate to <strong>Account Verification → Document Upload</strong>.</p>
    <p style="margin-bottom: 25px; padding: 15px; background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 4px; color: #92400e;"><strong>Note:</strong> Your shop will remain in <em>Pending Approval</em> status until these documents are reviewed by our team (typically within 48 hours).</p>
    <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 30px 0;">
    <p style="margin-bottom: 5px; color: #6b7280; font-size: 14px;">If you have any questions, please reach out to us at <a href="mailto:[[admin_email]]" style="color: #0b60bd; text-decoration: none;">[[admin_email]]</a>.</p>
    <p style="margin-top: 20px; color: #111827; font-weight: 500;">Best regards,<br>The [[store_name]] Team</p>
</div>',
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
