<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OtpService;
use App\Models\User;
use Hash;
use Auth;

class OTPVerificationController extends Controller
{
    /**
     * Send verification code to user.
     *
     * @param User $user
     * @return void
     */
    public function send_code(User $user)
    {
        (new OtpService)->send_code($user);
    }

    /**
     * Show the verification form.
     */
    public function verification()
    {
        return view('frontend.otp_verification');
    }

    /**
     * Verify the phone record with code.
     */
    public function verify_phone(Request $request)
    {
        $user = Auth::user();
        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:m:s');
            $user->save();
            offerUserWelcomeCoupon();
            flash(translate('Verification successful.'))->success();
            return redirect()->route('dashboard');
        }

        flash(translate('Invalid verification code.'))->error();
        return back();
    }
    
    /**
     * Resend verification code.
     */
    public function resend_verificcation_code()
    {
        $user = Auth::user();
        $user->verification_code = rand(100000, 999999);
        $user->save();

        $this->send_code($user);
        flash(translate('Verification code resent.'))->success();
        return back();
    }

    /**
     * Show reset password form for phone.
     */
    public function show_reset_password_form()
    {
        return view('frontend.passwords.phone_reset');
    }

    /**
     * Reset password with code.
     */
    public function reset_password_with_code(Request $request)
    {
        $user = User::where(['phone' => $request->phone, 'verification_code' => $request->code])->first();
        if ($user != null) {
            $user->password = Hash::make($request->password);
            $user->verification_code = rand(100000, 999999);
            $user->save();
            Auth::login($user);
            flash(translate('Password has been reset successfully'))->success();
            return redirect()->route('home');
        }

        flash(translate('Invalid phone or code'))->error();
        return back();
    }
}
