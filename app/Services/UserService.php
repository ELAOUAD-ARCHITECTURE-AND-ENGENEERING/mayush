<?php

namespace App\Services;

use App\Models\User;
use App\Models\RegistrationVerificationCode;
use App\Models\SmsTemplate;
use App\Utility\EmailUtility;
use Hash;
use Auth;
use File;

class UserService
{
    /**
     * Update user profile information.
     */
    public function updateProfile(User $user, array $data)
    {
        $user->name = $data['name'];
        $user->address = $data['address'] ?? $user->address;
        $user->country = $data['country'] ?? $user->country;
        $user->city = $data['city'] ?? $user->city;
        $user->postal_code = $data['postal_code'] ?? $user->postal_code;
        $user->phone = $data['phone'] ?? $user->phone;

        if (!empty($data['new_password']) && ($data['new_password'] == $data['confirm_password'])) {
            $user->password = Hash::make($data['new_password']);
        }

        if (isset($data['photo'])) {
            $user->avatar_original = $data['photo'];
        }

        return $user->save();
    }

    /**
     * Update user verification documents.
     */
    public function updateVerificationInfo(User $user, array $files, array $data)
    {
        $verification_info = json_decode($user->verification_info, true) ?? [];

        if (isset($files['id_card'])) {
            $this->deleteOldFile($verification_info['id_card'] ?? null);
            $verification_info['id_card'] = $files['id_card']->store('uploads/verification_form');
        }

        if (isset($files['customer_photo'])) {
            $this->deleteOldFile($verification_info['customer_photo'] ?? null);
            $verification_info['customer_photo'] = $files['customer_photo']->store('uploads/verification_form');
        }

        if (!empty($data['live_selfie'])) {
            $this->deleteOldFile($verification_info['customer_selfie'] ?? null);
            $imageName = 'uploads/verification_form/customer_selfie_' . time() . '.png';
            $image = str_replace(['data:image/png;base64,', ' '], ['', '+'], $data['live_selfie']);
            File::put(public_path($imageName), base64_decode($image));
            $verification_info['customer_selfie'] = $imageName;
        }

        $user->verification_info = json_encode($verification_info);
        return $user->save();
    }

    /**
     * Send registration verification code.
     */
    public function sendRegistrationCode($email, $phone, $countryCode)
    {
        $verificationCode = rand(100000, 999999);
        $fullPhone = $phone ? '+' . $countryCode . preg_replace('/\D+/', '', $phone) : null;

        RegistrationVerificationCode::updateOrCreate(
            ['email' => $email, 'phone' => $fullPhone],
            ['code' => $verificationCode]
        );

        if ($email) {
            try {
                EmailUtility::email_verification_for_registration_customer('email_verification_for_registration_customer', $email, $verificationCode);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        } elseif ($fullPhone && addon_is_activated('otp_system')) {
            $sms_template = SmsTemplate::where('identifier', 'phone_number_verification')->first();
            $sms_body = str_replace(['[[code]]', '[[site_name]]'], [$verificationCode, env('APP_NAME')], $sms_template->sms_body);
            (new SendSmsService())->sendSMS($fullPhone, env('APP_NAME'), $sms_body, $sms_template->template_id);
            return true;
        }

        return false;
    }

    /**
     * Verify the registration code.
     */
    public function verifyRegistrationCode($code, $email = null, $phone = null)
    {
        $query = RegistrationVerificationCode::where('code', $code);
        if ($email) {
            $query->where('email', $email);
        } else {
            $query->where('phone', $phone);
        }

        $verification = $query->first();

        if ($verification) {
            $verification->is_verified = 1;
            $verification->save();
            return true;
        }

        return false;
    }

    /**
     * Delete old file if exists.
     */
    protected function deleteOldFile($path)
    {
        if ($path && file_exists(public_path($path))) {
            unlink(public_path($path));
        }
    }
}
