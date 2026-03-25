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
        if ($user->phone != null && addon_is_activated('otp_system')) {
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
        $text = translate('Your verification code is') . ' : ' . $user->verification_code;
        SendSMSUtility::sendSMS($user->phone, get_setting('site_name'), $text, get_setting('otp_template_id'));
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
