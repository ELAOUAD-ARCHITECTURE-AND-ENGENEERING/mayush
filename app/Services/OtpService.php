<?php

namespace App\Services;

use App\Models\User;
use App\Models\BusinessSetting;
use App\Utility\SendSMSUtility;
use App\Notifications\AppEmailVerificationNotification;
use Illuminate\Support\Facades\Notification;

class OtpService
{
    /**
     * Send OTP to user via SMS or Email based on settings.
     *
     * @param User $user
     * @return void
     */
    public function send_code(User $user)
    {
        if ($user->phone != null && get_setting('otp_system') == 1) {
            $this->send_code_via_sms($user);
        } elseif ($user->email != null) {
            $this->send_code_via_email($user);
        }
    }

    /**
     * Send OTP via SMS.
     *
     * @param User $user
     * @return void
     */
    protected function send_code_via_sms(User $user)
    {
        $sms_template = \App\Models\SmsTemplate::where('identifier', 'otp_verification')->first();
        $sms_body = $sms_template ? $sms_template->sms_body : translate('Your verification code is') . ' : [[code]]';
        $sms_body = str_replace('[[code]]', $user->verification_code, $sms_body);
        $sms_body = str_replace('[[site_name]]', get_setting('site_name'), $sms_body);
        
        $template_id = $sms_template ? $sms_template->template_id : get_setting('otp_template_id');

        SendSMSUtility::sendSMS($user->phone, get_setting('site_name'), $sms_body, $template_id);
    }

    /**
     * Send OTP via Email.
     *
     * @param User $user
     * @return void
     */
    protected function send_code_via_email(User $user)
    {
        try {
            $user->notify(new AppEmailVerificationNotification());
        } catch (\Exception $e) {
            // Log error or handle silently as in AuthController
        }
    }
}
