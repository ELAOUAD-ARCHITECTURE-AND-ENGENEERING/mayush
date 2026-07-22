<?php

namespace App\Utility;

use App\Mail\MailManager;
use App\Models\EmailTemplate;
use App\Models\User;
use Mail;

class EmailUtility
{
    // Customer registration email to Admin & Customer
    public static function customer_registration_email($emailIdentifier, $user, $password = null){
        $admin = get_admin();
        $emailSendTo = $emailIdentifier == 'customer_reg_email_to_admin' ? $admin->email : $user->email;
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[customer_name]]', $user->name, $emailSubject);
        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);

        $emailBody = $emailTemplate->default_text;
        $email_or_phone = $user->email != null ? $user->email : $user->phone;
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[admin_name]]', $admin->name, $emailBody);
        $emailBody = str_replace('[[customer_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[email]]', $user->email, $emailBody);
        $emailBody = str_replace('[[password]]', $password, $emailBody);
        $emailBody = str_replace('[[email/phone]]', $email_or_phone, $emailBody);
        $emailBody = str_replace('[[date]]', date('d-m-Y', strtotime($user->created_at)), $emailBody);
        $emailBody = str_replace('[[admin_email]]', $admin?->email ?? '', $emailBody);
        
        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;

        Mail::to($emailSendTo)->queue(new MailManager($array));
    }

     // Email verification for customer Registration
     public static function email_verification_for_registration_customer($emailIdentifier, $email, $verificationCode){
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);

        $emailBody = $emailTemplate->default_text;
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[code]]', $verificationCode, $emailBody);
        $emailBody = str_replace('[[admin_email]]', get_admin()->email, $emailBody);

        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;

        Mail::to($email)->queue(new MailManager($array));
    }

    // Email verification for seller Registration
    public static function email_verification_for_registration_seller($emailIdentifier, $email, $verificationCode){
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);

        $emailBody = $emailTemplate->default_text;
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[code]]', $verificationCode, $emailBody);
        $emailBody = str_replace('[[admin_email]]', get_admin()->email, $emailBody);

        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;

        Mail::to($email)->queue(new MailManager($array));
    }


    // Customer wallet recharge to Admin & Customer
    public static function wallet_recharge_email($emailIdentifier, $user, $amount, $payment_method){
        $admin = get_admin();
        $emailSendTo = $user->email;
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[amount]]', $amount, $emailSubject);

        $emailBody = $emailTemplate->default_text;
        $email_or_phone = $user->email != null ? $user->email : $user->phone;
        $emailBody = str_replace('[[payment_method]]', $payment_method, $emailBody);
        $emailBody = str_replace('[[amount]]', $amount, $emailBody);
        $emailBody = str_replace('[[date]]', date('d-m-Y', strtotime($user->created_at)), $emailBody);
        $emailBody = str_replace('[[admin_email]]', $admin?->email ?? '', $emailBody);
        $emailBody = str_replace('[[customer_name]]', $user->name, $emailBody);
        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;

        Mail::to($emailSendTo)->queue(new MailManager($array));
    }
    
    // Seller registration email to Admin & Seller
    public static function seller_registration_email($emailIdentifier, $user, $password = null){
        $admin = get_admin();
        $shop = $user->shop;
        $emailSendTo = $emailIdentifier == 'seller_reg_email_to_admin' ? $admin->email : $user->email;
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();
        if (!$emailTemplate || $emailTemplate->status == 0) return;

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[shop_name]]', $shop->name, $emailSubject);
        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);

        $emailBody = $emailTemplate->default_text;
        $emailBody = str_replace('[[admin_name]]', $admin->name, $emailBody);
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[seller_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[seller_email]]', $user->email, $emailBody);
        $emailBody = str_replace('[[password]]', $password, $emailBody);
        $emailBody = str_replace('[[seller_shop_name]]', $shop->name, $emailBody);
        $emailBody = str_replace('[[seller_shop_address]]', $shop->address, $emailBody);
        $emailBody = str_replace('[[date]]', date('d-m-Y', strtotime($user->created_at)), $emailBody);
        $emailBody = str_replace('[[admin_email]]', $admin?->email ?? '', $emailBody);

        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;

        Mail::to($emailSendTo)->queue(new MailManager($array));
    }

    public static function deliveryBoyRegEmail($emailIdentifiers, $user, $password){
        $admin = get_admin();
        foreach($emailIdentifiers as $emailIdentifier){
            $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();
            if($emailTemplate != null && $emailTemplate->status == 1){
                
                $emailSendTo = $emailTemplate->receiver == 'admin' ? $admin->email : $user->email;

                $emailSubject = $emailTemplate->subject;
                $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);
    
                $emailBody = $emailTemplate->default_text;
                $emailBody = str_replace('[[admin_name]]', $admin->name, $emailBody);
                $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
                $emailBody = str_replace('[[delivery_boy_name]]', $user->name, $emailBody);
                $emailBody = str_replace('[[delivery_boy_email]]', $user->email, $emailBody);
                $emailBody = str_replace('[[delivery_boy_phone]]', $user->phone, $emailBody);
                $emailBody = str_replace('[[delivery_boy_password]]', $password, $emailBody);
                $emailBody = str_replace('[[delivery_boy_country]]', $user->country, $emailBody);
                $emailBody = str_replace('[[date]]', date('d-m-Y', strtotime($user->created_at)), $emailBody);
                $emailBody = str_replace('[[admin_email]]', $admin->email, $emailBody);
    
                $array['subject'] = $emailSubject;
                $array['content'] = $emailBody;
                
                try {
                    Mail::to($emailSendTo)->queue(new MailManager($array));
                } catch (\Exception $e) {
                    \Log::error("Email sending failed (deliveryBoyRegEmail): " . $e->getMessage());
                }
            }
        }
    }
 
    // Order delivery and payment status change Email
    public static function order_email($order, $status){
        $admin = get_admin();
        $userIds = array($order->seller_id);
        if($order->user->email != null){
            array_push($userIds, $order->user_id);
        }
        if ($order->seller_id != $admin->id) {
            array_push($userIds, $admin->id);
        }
        $users = User::findMany($userIds);

        foreach($users as $user){ 
            $emailIdentifier = 'order_'.$status.'_email_to_'.$user->user_type;
            $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

            if($emailTemplate != null && $emailTemplate->status == 1){
                $shopName = $user->user_type == 'seller' ? $user->shop->name : null;
                $emailSubject = $emailTemplate->subject;
                $emailSubject = str_replace('[[order_code]]', $order->code, $emailSubject);
    
                $emailBody = $emailTemplate->default_text;
                $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
                $emailBody = str_replace('[[shop_name]]', $shopName, $emailBody);
                $emailBody = str_replace('[[customer_name]]', $order->user->name, $emailBody);
                $emailBody = str_replace('[[admin_name]]', $admin->name, $emailBody);
                $emailBody = str_replace('[[order_code]]', $order->code, $emailBody);
                $emailBody = str_replace('[[order_date]]', date('d-m-Y', strtotime($order->created_at)), $emailBody);
                $emailBody = str_replace('[[delivery_date]]', date('d-m-Y'), $emailBody);
                $emailBody = str_replace('[[order_amount]]', single_price($order->grand_total), $emailBody);
                $emailBody = str_replace('[[admin_email]]', $admin->email, $emailBody);
                
                $array['subject'] = $emailSubject;
                $array['content'] = $emailBody;
    
                try {
                    Mail::to($user->email)->queue(new MailManager($array));
                } catch (\Exception $e) {
                    \Log::error("Email sending failed (order_email): " . $e->getMessage());
                }
            }   
        }  
    }

    // User Email Verification
    public static function email_verification($user, $userType){
        $emailIdentifier =  'email_verification_'.$userType;
        $verification_code = encrypt($user->id);

        // User Veridication code add
        $user->verification_code = $verification_code;
        $user->save();

        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);
        
        $emailBody = $emailTemplate->default_text;
        $link = route('email.verification.confirmation', $verification_code);
        $verifyButton = '<div style="display: flex; justify-content: center; padding-bottom:4px;">
            <a href="'.$link.'" target="_blank" style="background: #0b60bd; text-decoration:none; padding: 1.4rem 2rem; color:#fff;border-radius: .3rem;">Cliquez ici pour vérifier votre compte</a>
        </div>';
        
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[customer_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[seller_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[verify_email_button]]', $verifyButton, $emailBody);
        $emailBody = str_replace('[[admin_email]]', get_admin()->email, $emailBody);

        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;

        Mail::to($user->email)->queue(new MailManager($array));

    }


     //Update Email OTP verification for customer Registration
     public static function email_otp_verification_for_update_email($user, $userType, $verificationCode, $new_email){

        $emailIdentifier =  'change_email_verification_code_'.$userType;
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);

        $emailBody = $emailTemplate->default_text;
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[seller_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[customer_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[code]]', $verificationCode, $emailBody);
        $emailBody = str_replace('[[admin_email]]', get_admin()->email, $emailBody);
        $emailBody = str_replace('[[new_email]]', $new_email, $emailBody);
        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;

        Mail::to($user->email)->queue(new MailManager($array));
    }


    // Update Email Verification
    public static function change_email_verification($user, $userType, $new_email){
      
        $emailIdentifier =  'email_update_verification_'.$userType;
        $verification_code = encrypt($user->id);

        // User Veridication code add
        $user->new_email_verificiation_code = $verification_code;
        $user->save();
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);
        
        $emailBody = $emailTemplate->default_text;
        $link = route('email_change.callback') 
        . '?new_email_verificiation_code=' . urlencode($verification_code) 
        . '&email=' . urlencode($new_email);
        $verifyButton = '<div style="display: flex; justify-content: center; padding-bottom:4px;">
            <a href="'.$link.'" target="_blank" style="background: #0b60bd; text-decoration:none; padding: 1.4rem 2rem; color:#fff;border-radius: .3rem;">Click here</a>
        </div>';
        
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[customer_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[seller_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[verify_email_button]]', $verifyButton, $emailBody);
        $emailBody = str_replace('[[admin_email]]', get_admin()->email, $emailBody);

        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;
        Mail::to($new_email)->queue(new MailManager($array));

    }

    // Seller Payout emails
    public static function seller_payout($emailIdentifiers, $seller, $amount, $payment_method = null){
        $admin = get_admin();
        $shop = $seller->shop;
        foreach($emailIdentifiers as $emailIdentifier){
            $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();
            if($emailTemplate != null && $emailTemplate->status == 1){
                $emailSendTo = $emailTemplate->receiver == 'admin' ? $admin->email : $seller->email;

                $emailSubject = $emailTemplate->subject;
                $emailSubject = str_replace('[[shop_name]]', $shop->name, $emailSubject);
                $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailSubject);

                $emailBody = $emailTemplate->default_text;
                $emailBody = str_replace('[[admin_name]]', $admin->name, $emailBody);
                $emailBody = str_replace('[[shop_name]]', $shop->name, $emailBody);
                $emailBody = str_replace('[[shop_email]]', $seller->email, $emailBody);
                $emailBody = str_replace('[[amount]]', single_price($amount), $emailBody);
                $emailBody = str_replace('[[payment_method]]', $payment_method, $emailBody);
                $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);;
                $emailBody = str_replace('[[date]]', date('d-m-Y'), $emailBody);
                $emailBody = str_replace('[[admin_email]]', $admin->email, $emailBody);

                $array['subject'] = $emailSubject;
                $array['content'] = $emailBody;

                try {
                    Mail::to($emailSendTo)->queue(new MailManager($array));
                } catch (\Exception $e) {
                    \Log::error("Email sending failed (seller_payout): " . $e->getMessage());
                }
            }
        
        }
    }

    // Refund Request Email
    public static function refundEmail($emailIdentifiers, $refundReqest){
        $order = $refundReqest->order;
        $customer = $refundReqest->user;
        $seller = $refundReqest->seller;
        $productName = $refundReqest->orderDetail->product->getTranslation('name');
        $shopName = $refundReqest?->order?->shop?->user->user_type == 'seller' ? $refundReqest?->order?->shop->name : null;

        $admin = get_admin();
        foreach($emailIdentifiers as $emailIdentifier){
            $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();
            if($emailTemplate != null && $emailTemplate->status == 1){
                
                if($emailTemplate->receiver == 'admin'){
                    $emailSendTo = $admin->email;
                }
                elseif($emailTemplate->receiver == 'seller'){
                    $emailSendTo = $seller->email;
                }
                elseif($emailTemplate->receiver == 'customer'){
                    $emailSendTo = $customer->email;
                }
                $emailSubject = $emailTemplate->subject;
                $emailSubject = str_replace('[[order_code]]', $order->code, $emailSubject);
                $emailSubject = str_replace('[[shop_name]]', $shopName, $emailSubject);
    
                $emailBody = $emailTemplate->default_text;
                $emailBody = str_replace('[[admin_name]]', $admin->name, $emailBody);
                $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
                $emailBody = str_replace('[[shop_name]]', $shopName, $emailBody);
                $emailBody = str_replace('[[customer_name]]', $customer->name, $emailBody);
                $emailBody = str_replace('[[order_code]]', $order->code, $emailBody);
                $emailBody = str_replace('[[product_name]]', $productName, $emailBody);
                $emailBody = str_replace('[[refund_reason]]', $refundReqest->reason, $emailBody);
                $emailBody = str_replace('[[denied_reason]]', $refundReqest->reject_reason, $emailBody);
                $emailBody = str_replace('[[request_date]]', date('d-m-Y', strtotime($refundReqest->created_at)), $emailBody);
                $emailBody = str_replace('[[refund_amount]]', single_price($refundReqest->refund_amount), $emailBody);
                $emailBody = str_replace('[[processes_date]]', date('d-m-Y'), $emailBody);
                $emailBody = str_replace('[[admin_email]]', $admin->email, $emailBody);
    
                $array['subject'] = $emailSubject;
                $array['content'] = $emailBody;
                
                try {
                    Mail::to($emailSendTo)->queue(new MailManager($array));
                } catch (\Exception $e) {
                    \Log::error("Email sending failed (refundEmail): " . $e->getMessage());
                }
            }
        }
    }

     // Seller registration email to Admin & Seller
    public static function seller_shop_approval_email($emailIdentifier, $shop){
        $admin = get_admin();
        $shop = $shop;
        $user= $shop->user;
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        $emailSubject = $emailTemplate->subject;
        $emailSubject = str_replace('[[seller_shop_name]]', $shop->name, $emailSubject);

        $emailBody = $emailTemplate->default_text;
        $emailBody = str_replace('[[admin_name]]', $admin->name, $emailBody);
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[seller_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[seller_email]]', $user->email, $emailBody);
        $emailBody = str_replace('[[seller_shop_name]]', $shop->name, $emailBody);
        $emailBody = str_replace('[[seller_shop_address]]', $shop->address, $emailBody);
        $emailBody = str_replace('[[date]]', date('d-m-Y', strtotime($user->created_at)), $emailBody);
        $emailBody = str_replace('[[admin_email]]', $admin->email, $emailBody);
        $emailBody = str_replace('[[login_url]]', route('seller.login'), $emailBody);
        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;
        Mail::to($user->email)->queue(new MailManager($array));
    }

    public static function abandoned_cart_reminder($order)
    {
        $user = $order->user;
        if (!$user || !$user->email) return;

        $array['subject'] = 'Votre panier vous attend - ' . get_setting('site_name');
        $array['view'] = 'emails.abandoned_cart';
        $array['order'] = $order;

        \Mail::send($array['view'], ['order' => $order], function($message) use ($array, $user) {
            $message->to($user->email)->subject($array['subject']);
        });
    }

    public static function seller_onboarding_request_email($shop)
    {
        $admin = get_admin();
        $user = $shop->user;
        $emailTemplate = EmailTemplate::whereIdentifier('seller_onboarding_documents_request')->first();

        if (!$emailTemplate || $emailTemplate->status == 0) return;

        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailTemplate->subject);
        $emailBody = $emailTemplate->default_text;
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[seller_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[admin_email]]', $admin?->email ?? '', $emailBody);

        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;
        Mail::to($user->email)->queue(new MailManager($array));
    }

    public static function seller_documents_received_admin($emailIdentifier, $shop)
    {
        $admin = get_admin();
        $user = $shop->user;
        $emailTemplate = EmailTemplate::whereIdentifier($emailIdentifier)->first();

        if (!$emailTemplate || $emailTemplate->status == 0) return;

        $emailSubject = str_replace('[[seller_shop_name]]', $shop->name, $emailTemplate->subject);

        $emailBody = $emailTemplate->default_text;
        $emailBody = str_replace('[[admin_name]]', $admin?->name ?? 'Administrator', $emailBody);
        $emailBody = str_replace('[[seller_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[seller_shop_name]]', $shop->name, $emailBody);
        $emailBody = str_replace('[[seller_email]]', $user->email, $emailBody);
        $emailBody = str_replace('[[date]]', $shop->documents_submitted_at ? $shop->documents_submitted_at->format('d-m-Y H:i') : now()->format('d-m-Y H:i'), $emailBody);
        $emailBody = str_replace('[[resubmission_count]]', $shop->resubmission_count, $emailBody);
        $emailBody = str_replace('[[admin_panel_url]]', route('sellers.registration_pending'), $emailBody);
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);

        $recipients = User::query()
            ->whereIn('user_type', ['admin', 'staff'])
            ->where('banned', 0)
            ->whereNotNull('email')
            ->get();

        if ($recipients->isEmpty() && $admin?->email) {
            $recipients = collect([$admin]);
        }

        foreach ($recipients as $recipient) {
            $recipientBody = str_replace('[[admin_name]]', $recipient->name, $emailBody);
            Mail::to($recipient->email)->queue(new MailManager([
                'subject' => $emailSubject,
                'content' => $recipientBody,
            ]));
        }
    }

    public static function seller_application_status_email($shop, $status, $reason = null)
    {
        $admin = get_admin();
        $user = $shop->user;
        $identifier = $status === 'approved' ? 'seller_application_approved' : 'seller_application_rejected';
        $emailTemplate = EmailTemplate::whereIdentifier($identifier)->first();

        if (!$emailTemplate || $emailTemplate->status == 0) return;

        $emailSubject = str_replace('[[store_name]]', get_setting('site_name'), $emailTemplate->subject);

        $emailBody = $emailTemplate->default_text;
        $emailBody = str_replace('[[seller_name]]', $user->name, $emailBody);
        $emailBody = str_replace('[[seller_shop_name]]', $shop->name, $emailBody);
        $emailBody = str_replace('[[store_name]]', get_setting('site_name'), $emailBody);
        $emailBody = str_replace('[[admin_email]]', $admin?->email ?? '', $emailBody);
        $emailBody = str_replace('[[login_url]]', route('seller.login'), $emailBody);

        if ($status === 'rejected') {
            $emailBody = str_replace('[[rejection_reason]]', $reason ?? 'No specific reason provided.', $emailBody);
            $attemptsRemaining = max(0, 10 - $shop->resubmission_count);
            $emailBody = str_replace('[[resubmission_attempts_remaining]]', $attemptsRemaining, $emailBody);
        }

        $array['subject'] = $emailSubject;
        $array['content'] = $emailBody;
        Mail::to($user->email)->queue(new MailManager($array));
    }

    /**
     * Send a seller onboarding lifecycle email using an idempotently seeded
     * template. Unknown placeholders are intentionally left untouched so
     * administrators can identify incomplete template configuration.
     */
    public static function seller_onboarding_event_email($shop, string $identifier, array $context = []): void
    {
        $user = $shop->user;
        if (!$user || !$user->email) {
            return;
        }

        $emailTemplate = EmailTemplate::whereIdentifier($identifier)->first();
        if (!$emailTemplate || $emailTemplate->status == 0) {
            return;
        }

        $admin = get_admin();
        $replacements = [
            '[[store_name]]' => get_setting('site_name'),
            '[[seller_name]]' => $user->name,
            '[[seller_shop_name]]' => $shop->name,
            '[[seller_email]]' => $user->email,
            '[[admin_email]]' => $admin?->email ?? '',
            '[[approval_status]]' => $shop->approvalStatusLabel(),
            '[[onboarding_url]]' => route('seller.onboarding.index'),
            '[[dashboard_url]]' => route('seller.dashboard'),
            '[[document_id]]' => (string) ($context['document_id'] ?? ''),
            '[[document_type]]' => translate(ucwords(str_replace('_', ' ', (string) ($context['document_type'] ?? 'document')))),
            '[[document_version]]' => (string) ($context['document_version'] ?? ''),
            '[[reason]]' => (string) ($context['reason'] ?? ''),
        ];

        $subject = str_replace(array_keys($replacements), array_values($replacements), $emailTemplate->subject);
        $bodyTemplate = $emailTemplate->default_text ?? $emailTemplate->content ?? '';
        $body = str_replace(array_keys($replacements), array_values($replacements), $bodyTemplate);

        Mail::to($user->email)->queue(new MailManager([
            'subject' => $subject,
            'content' => $body,
        ]));
    }

    /**
     * Send refund request submission email to customer, seller, and admin.
     */
    public static function sendRefundRequestEmails($refundRequest): void
    {
        $order = $refundRequest->order;
        $customer = $refundRequest->user ?? $order?->user;
        $seller = User::find($refundRequest->seller_id);
        $admin = get_admin();

        $replacements = [
            '[[store_name]]' => get_setting('site_name'),
            '[[customer_name]]' => $customer?->name ?? '',
            '[[seller_name]]' => $seller?->name ?? '',
            '[[shop_name]]' => $seller?->shop?->name ?? get_setting('site_name'),
            '[[order_code]]' => $order?->code ?? '',
            '[[refund_amount]]' => single_price($refundRequest->refund_amount),
            '[[admin_email]]' => $admin?->email ?? '',
        ];

        self::sendTemplateEmail('refund_request_email_to_customer', $customer?->email, $replacements);

        if ($seller && $seller->email) {
            self::sendTemplateEmail('refund_request_email_to_seller', $seller->email, $replacements);
        }

        if ($admin && $admin->email) {
            self::sendTemplateEmail('refund_request_email_to_admin', $admin->email, $replacements);
        }
    }

    /**
     * Send refund request accepted email to customer, seller, and admin.
     */
    public static function sendRefundAcceptedEmails($refundRequest, string $byRole = 'admin'): void
    {
        $order = $refundRequest->order;
        $customer = $refundRequest->user ?? $order?->user;
        $seller = User::find($refundRequest->seller_id);
        $admin = get_admin();

        $replacements = [
            '[[store_name]]' => get_setting('site_name'),
            '[[customer_name]]' => $customer?->name ?? '',
            '[[seller_name]]' => $seller?->name ?? '',
            '[[shop_name]]' => $seller?->shop?->name ?? get_setting('site_name'),
            '[[order_code]]' => $order?->code ?? '',
            '[[refund_amount]]' => single_price($refundRequest->refund_amount),
            '[[admin_email]]' => $admin?->email ?? '',
        ];

        self::sendTemplateEmail('refund_request_accepted_email_to_customer', $customer?->email, $replacements);

        $adminIdentifier = $byRole === 'seller' ? 'refund_accepted_by_seller_email_to_admin' : 'refund_accepted_by_admin_email_to_admin';
        if ($admin && $admin->email) {
            self::sendTemplateEmail($adminIdentifier, $admin->email, $replacements);
        }

        $sellerIdentifier = $byRole === 'seller' ? 'refund_accepted_by_seller_email_to_seller' : 'refund_accepted_by_admin_email_to_seller';
        if ($seller && $seller->email) {
            self::sendTemplateEmail($sellerIdentifier, $seller->email, $replacements);
        }
    }

    /**
     * Send refund request denied email to customer, seller, and admin.
     */
    public static function sendRefundDeniedEmails($refundRequest, string $byRole = 'admin'): void
    {
        $order = $refundRequest->order;
        $customer = $refundRequest->user ?? $order?->user;
        $seller = User::find($refundRequest->seller_id);
        $admin = get_admin();

        $replacements = [
            '[[store_name]]' => get_setting('site_name'),
            '[[customer_name]]' => $customer?->name ?? '',
            '[[seller_name]]' => $seller?->name ?? '',
            '[[shop_name]]' => $seller?->shop?->name ?? get_setting('site_name'),
            '[[order_code]]' => $order?->code ?? '',
            '[[refund_amount]]' => single_price($refundRequest->refund_amount),
            '[[rejection_reason]]' => $refundRequest->reject_reason ?? 'N/A',
            '[[admin_email]]' => $admin?->email ?? '',
        ];

        self::sendTemplateEmail('refund_request_denied_email_to_customer', $customer?->email, $replacements);

        $adminIdentifier = $byRole === 'seller' ? 'refund_denied_by_seller_email_to_admin' : 'refund_denied_by_admin_email_to_admin';
        if ($admin && $admin->email) {
            self::sendTemplateEmail($adminIdentifier, $admin->email, $replacements);
        }

        $sellerIdentifier = $byRole === 'seller' ? 'refund_denied_by_seller_email_to_seller' : 'refund_denied_by_admin_email_to_seller';
        if ($seller && $seller->email) {
            self::sendTemplateEmail($sellerIdentifier, $seller->email, $replacements);
        }
    }

    /**
     * Generic email template dispatcher by identifier and placeholder replacements.
     */
    public static function sendTemplateEmail(string $identifier, ?string $toEmail, array $replacements = []): void
    {
        if (!$toEmail) {
            return;
        }

        $template = EmailTemplate::whereIdentifier($identifier)->first();
        if (!$template || $template->status == 0) {
            return;
        }

        $subject = str_replace(array_keys($replacements), array_values($replacements), $template->subject ?? '');
        $bodyText = $template->default_text ?? $template->content ?? '';
        $body = str_replace(array_keys($replacements), array_values($replacements), $bodyText);

        try {
            Mail::to($toEmail)->queue(new MailManager([
                'subject' => $subject,
                'content' => $body,
            ]));
            \Log::info("Email template {$identifier} dispatched to {$toEmail}");
        } catch (\Exception $e) {
            \Log::error("Failed to send email template {$identifier} to {$toEmail}: " . $e->getMessage());
        }
    }
}
