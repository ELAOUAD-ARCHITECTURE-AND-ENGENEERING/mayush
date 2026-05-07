<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RegistrationVerificationCode;
use App\Utility\EmailUtility;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Rules\Recaptcha;

class CustomAuthController extends Controller
{
    public function login()
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        $authService = app(\App\Services\AuthService::class);
        return view($authService->getLoginView(\Illuminate\Support\Facades\Route::currentRouteName()));
    }

    public function registration(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        app(\App\Services\AuthService::class)->processRegistrationReferral($request);

        $email = null;
        $phone = null;
        return view('auth.' . safe_auth_layout_select() . '.user_registration', compact('email','phone'));
    }

    public function cart_login(Request $request)
    {
        if (app(\App\Services\AuthService::class)->authenticateUser($request->all(), $request->has('remember'))) {
            return back();
        }

        flash(translate('Invalid email or password!'))->warning();
        return back();
    }

    // Ajax call
    public function new_verify(Request $request)
    {
        $email = $request->email;
        if (isUnique($email) == '0') {
            $response['status'] = 2;
            $response['message'] = translate('Email already exists!');
            return json_encode($response);
        }

        $response = $this->send_email_change_verification_mail($request, $email);
        return json_encode($response);
    }

    // Form request
    public function update_email(Request $request)
    {
        $email = $request->email;
        if (isUnique($email)) {
            $customerVerification = RegistrationVerificationCode::where('code', $request->code);
            $customerVerification = $customerVerification->where('email', $email);
            $customerVerification = $customerVerification->first();
            if ($customerVerification == null) {
                flash(translate('Verification code do not matched'))->error();
                return back();
            } else {
                $this->send_email_change_verification_mail($request, $email);
                flash(translate('A verification mail has been sent to the new email address you provided.'))->success();
                return back();
            }
        }

        flash(translate('Email already exists!'))->warning();
        return back();
    }

    public function send_email_change_verification_mail($request, $email)
    {
        $user = auth()->user();
        $response['status'] = 0;
        $response['message'] = 'Unknown';
        try {
            EmailUtility::change_email_verification($user, $user->user_type, $email);
            $response['status'] = 1;
            $response['message'] = translate("A verification mail has been sent to your new mail you provided us with.");
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function email_change_callback(Request $request)
    {
        if ($request->has('new_email_verificiation_code') && $request->has('email')) {
            $verification_code_of_url_param =  $request->input('new_email_verificiation_code');
            $user = User::where('new_email_verificiation_code', $verification_code_of_url_param)->first();

            if ($user != null) {

                $user->email = $request->input('email');
                $user->new_email_verificiation_code = null;
                $user->save();

                auth()->login($user, true);

                flash(translate('Email Changed successfully'))->success();
                if ($user->user_type == 'seller') {
                    return redirect()->route('seller.dashboard');
                }
                return redirect()->route('dashboard');
            }
        }

        flash(translate('Email was not verified. Please resend your mail!'))->error();
        return redirect()->route('dashboard');
    }

    public function reset_password_with_code(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ]);

        $user = User::where('email', $request->email)
            ->where('verification_code', $request->code)
            ->first();

        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);

        if ($user === null || $user->updated_at?->lt(now()->subMinutes($expiryMinutes))) {
            flash(translate("Verification code mismatch"))->error();
            $email = $request->email;
            return view('auth.'.safe_auth_layout_select().'.reset_password', compact('email'));
        }

        $user->password = Hash::make($request->password);
        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->save();
        event(new PasswordReset($user));
        auth()->login($user, true);
        \Auth::logoutOtherDevices($request->password);

        flash(translate('Password updated successfully'))->success();

        if (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home');
    }

    public function sendRegVerificationCode(Request $request, \App\Services\UserService $userService)
    {
         $request->validate([
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_register') == 1, ['required', new Recaptcha()], ['sometimes'])
            ],
        ]);

        $email = $request->email ?? null;
        $phone = $request->phone;

        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (User::where('email', $email)->first() != null) {                
                return response()->json(['status' => 0, 'message' => translate('Email already exists.')]);
            }
        } elseif ($phone) {
            $formattedPhone = '+' . $request->country_code . preg_replace('/\D+/', '', $phone);
            if (User::where('phone', $formattedPhone)->first() != null) {
                return response()->json(['status' => 0, 'message' => translate('Phone already exists.')]);
            }
        }

        if ($userService->sendRegistrationCode($email, $phone, $request->country_code)) {
            return response()->json(['status' => 1, 'message' => translate('Verification code sent successfully.')]);
        } else {
            return response()->json(['status' => 0, 'message' => translate('Verification code sending failed.')]);
        }
    }

    public function regVerifyCode($id)
    {
        $customerVerification = RegistrationVerificationCode::whereId(decrypt($id))->first();
        return view('auth.' . safe_auth_layout_select() . '.customer_verify_confirmation', compact('customerVerification'));
    }

    public function regVerifyCodeConfirmation(Request $request, \App\Services\UserService $userService)
    {
        $email = $request->email ?? null;
        $phone = $request->phone ? '+' . $request->country_code . $request->phone : null;

        if ($userService->verifyRegistrationCode($request->verification_code, $email, $phone)) {
            return response()->json(['status' => 1, 'message' => translate('Verification Successful')]);
        } else {
            return response()->json(['status' => 0, 'message' => translate('Verification Code did not match')]);
        }
    }

    public function sendEmailUpdateVerificationCode(Request $request)
    {
        $user = auth()->user();
        $phone = $request->phone != null ? '+' . $request->country_code . $request->phone : null;
        $email = $request->email;
        if (isUnique($email) == '0') {
            $response['status'] = 2;
            $response['message'] = translate('Email already exists!');
            return json_encode($response);
        }

        $verificationCode = rand(100000, 999999);
        $customerVerification = RegistrationVerificationCode::updateOrCreate(
            ['email' => $email, 'phone' => $phone],
            ['code' => $verificationCode]
        );

        try {
            EmailUtility::email_otp_verification_for_update_email($user, $user->user_type, $verificationCode, $email);
            $response['status'] = 1;
            $response['message'] = translate("We've sent a verification code to your previous email address.");
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['message'] = $e->getMessage();
        }
        return json_encode($response);
    }
}
