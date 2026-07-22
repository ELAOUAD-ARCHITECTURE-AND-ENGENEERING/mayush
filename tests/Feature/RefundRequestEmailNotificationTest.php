<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\RefundRequest;
use App\Models\User;
use App\Utility\EmailUtility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class RefundRequestEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('refund_requests')) {
            Schema::create('refund_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('order_detail_id')->nullable();
                $table->unsignedBigInteger('seller_id')->nullable();
                $table->text('reason')->nullable();
                $table->text('reject_reason')->nullable();
                $table->double('refund_amount')->default(0);
                $table->tinyInteger('refund_status')->default(0);
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('refund_requests', 'reject_reason')) {
            Schema::table('refund_requests', function (Blueprint $table) {
                $table->text('reject_reason')->nullable();
            });
        }

        if (!Schema::hasTable('email_templates')) {
            Schema::create('email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('identifier')->nullable();
                $table->string('subject')->nullable();
                $table->text('content')->nullable();
                $table->text('default_text')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('email_templates', 'default_text')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->text('default_text')->nullable();
            });
        }

        foreach ([
            'refund_request_email_to_customer',
            'refund_request_email_to_seller',
            'refund_request_email_to_admin',
            'refund_request_accepted_email_to_customer',
            'refund_accepted_by_admin_email_to_admin',
            'refund_accepted_by_admin_email_to_seller',
            'refund_request_denied_email_to_customer',
            'refund_denied_by_admin_email_to_admin',
            'refund_denied_by_admin_email_to_seller',
        ] as $identifier) {
            \DB::table('email_templates')->updateOrInsert(
                ['identifier' => $identifier],
                ['subject' => "Subject {$identifier}", 'default_text' => "Body {$identifier}", 'status' => 1]
            );
        }
    }

    public function test_submitting_refund_request_dispatches_emails(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['user_type' => 'admin', 'email' => 'admin-refund@example.test']);
        $seller = User::factory()->create(['user_type' => 'seller', 'email' => 'seller-refund@example.test']);
        $customer = User::factory()->create(['user_type' => 'customer', 'email' => 'customer-refund@example.test']);

        $order = Order::withoutEvents(fn () => Order::factory()->create([
            'user_id' => $customer->id,
            'seller_id' => $seller->id,
        ]));

        $refund = new RefundRequest();
        $refund->user_id = $customer->id;
        $refund->order_id = $order->id;
        $refund->seller_id = $seller->id;
        $refund->refund_amount = 150.00;
        $refund->refund_status = 0;
        $refund->save();

        EmailUtility::sendRefundRequestEmails($refund);

        Mail::assertQueued(\App\Mail\MailManager::class, 3);
    }

    public function test_accepting_refund_request_dispatches_acceptance_emails(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['user_type' => 'admin', 'email' => 'admin-refund@example.test']);
        $seller = User::factory()->create(['user_type' => 'seller', 'email' => 'seller-refund@example.test']);
        $customer = User::factory()->create(['user_type' => 'customer', 'email' => 'customer-refund@example.test']);

        $order = Order::withoutEvents(fn () => Order::factory()->create([
            'user_id' => $customer->id,
            'seller_id' => $seller->id,
        ]));

        $refund = new RefundRequest();
        $refund->user_id = $customer->id;
        $refund->order_id = $order->id;
        $refund->seller_id = $seller->id;
        $refund->refund_amount = 150.00;
        $refund->refund_status = 1;
        $refund->save();

        EmailUtility::sendRefundAcceptedEmails($refund, 'admin');

        Mail::assertQueued(\App\Mail\MailManager::class, 3);
    }

    public function test_rejecting_refund_request_dispatches_denial_emails(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['user_type' => 'admin', 'email' => 'admin-refund@example.test']);
        $seller = User::factory()->create(['user_type' => 'seller', 'email' => 'seller-refund@example.test']);
        $customer = User::factory()->create(['user_type' => 'customer', 'email' => 'customer-refund@example.test']);

        $order = Order::withoutEvents(fn () => Order::factory()->create([
            'user_id' => $customer->id,
            'seller_id' => $seller->id,
        ]));

        $refund = new RefundRequest();
        $refund->user_id = $customer->id;
        $refund->order_id = $order->id;
        $refund->seller_id = $seller->id;
        $refund->refund_amount = 150.00;
        $refund->reject_reason = 'Item is not in original packaging';
        $refund->refund_status = 2;
        $refund->save();

        EmailUtility::sendRefundDeniedEmails($refund, 'admin');

        Mail::assertQueued(\App\Mail\MailManager::class, 3);
    }
}
