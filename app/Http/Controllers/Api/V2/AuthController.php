<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\OTPVerificationController;
use App\Mail\GuestAccountOpeningMailManager;
use App\Models\Address;
use App\Models\BusinessSetting;
use App\Models\LoginSecurityState;
use App\Models\SecurityChallenge;
use App\Models\SecurityEvent;
use App\Models\UserDevice;
use App\Notifications\DeviceVerificationNotification;
use App\Services\Security\SecurityEventService;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\AppEmailVerificationNotification;
use Carbon\Carbon;
use Hash;
use Socialite;
use App\Models\Cart;
use App\Rules\Recaptcha;
use App\Utility\EmailUtility;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;
use Mail;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $messages = array(
            'name.required' => translate('Name is required'),
            'email_or_phone.required' => $request->register_by == 'email' ? translate('Email is required') : translate('Phone is required'),
            'email_or_phone.email' => translate('Email must be a valid email address'),
            'email_or_phone.numeric' => translate('Phone must be a number.'),
            'email_or_phone.unique' => $request->register_by == 'email' ? translate('The email has already been taken') : translate('The phone has already been taken'),
            'password.required' => translate('Password is required'),
            'password.confirmed' => translate('Password confirmation does not match'),
            'password.min' => translate('Minimum 6 digits required for password')
        );
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required|min:6|confirmed',
            'email_or_phone' => [
                'required',
                Rule::when($request->register_by === 'email', ['email', 'unique:users,email']),
                Rule::when($request->register_by === 'phone', ['numeric', 'unique:users,phone']),
            ],
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_register') == 1 , ['required', new Recaptcha()], ['sometimes'])
            ]
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->all()
            ]);
        }

        $user = new User();
        $user->name = $request->name;
        if ($request->register_by == 'email') {

            $user->email = $request->email_or_phone;
        }
        if ($request->register_by == 'phone') {
            $user->phone = $request->email_or_phone;
        }
        $user->password = bcrypt($request->password);
        $user->verification_code = rand(100000, 999999);
        $user->save();


        $user->email_verified_at = null;
        if ($user->email != null) {
            if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                $user->email_verified_at = date('Y-m-d H:m:s');
            }
        }

        if ($user->email_verified_at == null) {
            if ($request->register_by == 'email') {
                try {
                    $user->notify(new AppEmailVerificationNotification());
                } catch (\Exception $e) {
                }
            } else {
                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            }
        }

        $user->save();
        //create token
        $user->createToken('tokens')->plainTextToken;

        $tempUserId = $request->has('temp_user_id') ? $request->temp_user_id : null;
        return $this->loginSuccess($user, '', $tempUserId);
    }

    public function resendCode()
    {
        $user = auth()->user();
        $user->verification_code = rand(100000, 999999);

        if ($user->email) {
            try {
                $user->notify(new AppEmailVerificationNotification());
            } catch (\Exception $e) {
            }
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }

        $user->save();

        return response()->json([
            'result' => true,
            'message' => translate('Verification code is sent again'),
        ], 200);
    }

    public function confirmCode(Request $request)
    {
        $user = auth()->user();

        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();
            return response()->json([
                'result' => true,
                'message' => translate('Your account is now verified'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('Code does not match, you can request for resending the code'),
            ], 200);
        }
    }

    public function login(Request $request)
    {
        $messages = array(
            'email.required' => $request->login_by == 'email' ? translate('Email is required') : translate('Phone is required'),
            'email.email' => translate('Email must be a valid email address'),
            'email.numeric' => translate('Phone must be a number.'),
            'password.required' => translate('Password is required'),
        );
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'login_by' => 'required',
            'email' => [
                'required',
                Rule::when($request->login_by === 'email', ['email', 'required']),
                Rule::when($request->login_by === 'phone', ['numeric', 'required']),
            ],
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting($request['recaptcha_action']) == 1, ['required', new Recaptcha()], ['sometimes'])
            ]
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'message' => $validator->errors()->all()
            ]);
        }

        $delivery_boy_condition = $request->has('user_type') && $request->user_type == 'delivery_boy';
        $seller_condition = $request->has('user_type') && $request->user_type == 'seller';
        $req_email = $request->email;

        if ($delivery_boy_condition) {
            $user = User::whereIn('user_type', ['delivery_boy'])
                ->where(function ($query) use ($req_email) {
                    $query->where('email', $req_email)
                        ->orWhere('phone', $req_email);
                })
                ->first();
        } elseif ($seller_condition) {
            $user = User::whereIn('user_type', ['seller'])
                ->where(function ($query) use ($req_email) {
                    $query->where('email', $req_email)
                        ->orWhere('phone', $req_email);
                })
                ->first();
        } else {
            $user = User::whereIn('user_type', ['customer'])
                ->where(function ($query) use ($req_email) {
                    $query->where('email', $req_email)
                        ->orWhere('phone', $req_email);
                })
                ->first();
        }
        // if (!$delivery_boy_condition) {
        if (!$delivery_boy_condition && !$seller_condition && $request->filled('identity_matrix')) {
            if (\App\Utility\PayhereUtility::create_wallet_reference($request->identity_matrix) == false) {
                return response()->json(['result' => false, 'message' => 'Identity matrix error', 'user' => null], 401);
            }
        }

        if ($user == null) {
            return response()->json(['result' => false, 'message' => translate('User not found'), 'user' => null], 401);
        }

        // Permanent ban check (unchanged)
        if ($user->banned) {
            return response()->json(['result' => false, 'code' => 'ACCESS_DENIED', 'message' => translate('User is banned'), 'user' => null], 403);
        }

        // Indefinite security hold (support-reversible, distinct from ban)
        if ($user->security_hold_at !== null) {
            return response()->json([
                'result' => false,
                'code' => 'ACCOUNT_SECURITY_HOLD',
                'message' => 'Votre compte est temporairement suspendu pour des raisons de sécurité. Veuillez contacter le support.',
                'user' => null,
            ], 403);
        }

        // Temporary lock check
        $securityState = LoginSecurityState::where('user_id', $user->id)->first();
        if ($securityState && $securityState->isLocked()) {
            return response()->json([
                'result' => false,
                'code' => 'ACCOUNT_TEMPORARILY_BLOCKED',
                'message' => 'Trop de tentatives de connexion. Veuillez réessayer plus tard.',
                'retry_after_seconds' => $securityState->remainingLockSeconds(),
                'user' => null,
            ], 403);
        }

        if (Hash::check($request->password, $user->password)) {
            // Successful login: reset all escalation state
            LoginSecurityState::where('user_id', $user->id)->delete();

            // Unusual activity detection
            $securityEvent = SecurityEventService::logLoginEvent($user, $request, 'login_success');
            if ($securityEvent->flagged && SecurityEventService::isEnforced()) {
                return response()->json([
                    'result' => false,
                    'code' => 'UNUSUAL_ACTIVITY_VERIFICATION_REQUIRED',
                    'message' => 'Activité inhabituelle détectée. Veuillez confirmer votre identité.',
                    'flag_reason' => $securityEvent->flag_reason,
                    'user' => null,
                ], 403);
            }

            // Device verification check
            $deviceId = $request->header('X-Device-Id');
            if ($deviceId) {
                $hasVerifiedDevices = UserDevice::where('user_id', $user->id)->whereNotNull('verified_at')->exists();

                if ($hasVerifiedDevices) {
                    $knownDevice = UserDevice::where('user_id', $user->id)
                        ->where('device_id', $deviceId)
                        ->whereNotNull('verified_at')
                        ->first();

                    if (!$knownDevice) {
                        // New unverified device — require verification
                        ['challenge' => $challenge, 'code' => $code] = SecurityChallenge::createForDevice($user, $deviceId);

                        try {
                            $user->notify(new DeviceVerificationNotification($code));
                        } catch (\Exception $e) {
                            // Log but don't block
                        }

                        return response()->json([
                            'result' => false,
                            'code' => 'DEVICE_VERIFICATION_REQUIRED',
                            'message' => 'Un code de vérification a été envoyé à votre adresse e-mail.',
                            'challenge_id' => $challenge->id,
                            'expires_at' => $challenge->expires_at->toIso8601String(),
                            'method' => $challenge->method,
                            'user' => null,
                        ], 403);
                    }

                    // Known device — update last_seen and continue
                    $knownDevice->update(['last_seen_at' => now()]);
                } else {
                    // First-ever login: auto-verify this device
                    UserDevice::create([
                        'user_id' => $user->id,
                        'device_id' => $deviceId,
                        'device_name' => $request->header('User-Agent', 'Unknown Device'),
                        'verified_at' => now(),
                        'last_seen_at' => now(),
                    ]);
                }
            }

            $tempUserId = $request->has('temp_user_id') ? $request->temp_user_id : null;
            return $this->loginSuccess($user, '', $tempUserId);
        }

        // Log failed login event
        SecurityEventService::logLoginEvent($user, $request, 'login_failed');

        // Failed password: increment failures and apply escalation
        $securityState = LoginSecurityState::firstOrCreate(
            ['user_id' => $user->id],
            ['consecutive_failures' => 0, 'escalation_level' => 0]
        );
        $securityState->consecutive_failures++;
        $securityState->applyEscalation();
        $securityState->save();

        // If escalation just locked the account, return the lock response
        if ($securityState->isLocked()) {
            return response()->json([
                'result' => false,
                'code' => 'ACCOUNT_TEMPORARILY_BLOCKED',
                'message' => 'Trop de tentatives de connexion. Veuillez réessayer plus tard.',
                'retry_after_seconds' => $securityState->remainingLockSeconds(),
                'user' => null,
            ], 403);
        }

        // If escalation triggered a security hold
        if ($user->fresh()->security_hold_at !== null) {
            return response()->json([
                'result' => false,
                'code' => 'ACCOUNT_SECURITY_HOLD',
                'message' => 'Votre compte est temporairement suspendu pour des raisons de sécurité. Veuillez contacter le support.',
                'user' => null,
            ], 403);
        }

        return response()->json(['result' => false, 'message' => translate('Unauthorized'), 'user' => null], 401);
    }

    public function user(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate('User not found')
            ], 401);
        }

        return response()->json([
            'result' => true,
            'id' => $user->id,
            'type' => $user->user_type,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'avatar_original' => uploaded_asset($user->avatar_original),
            'phone' => $user->phone,
            'city' => $user->city,
            'postal_code' => $user->postal_code,
            'address' => $user->address,
            'gender' => $user->gender ?? null,
            'birthDate' => $user->birth_date ?? null,
            'email_verified' => $user->email_verified_at != null
        ]);
    }

    public function logout(Request $request)
    {

        $user = request()->user();
        $user->tokens()->delete();

        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged out')
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'result' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => $validator->errors()->all(),
            ], 422);
        }

        $user = $request->user();
        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'result' => false,
                'code' => 'CURRENT_PASSWORD_INVALID',
                'message' => translate('Current password is incorrect'),
            ], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();
        $user->tokens()->delete();

        SecurityEventService::logEvent($user, $request, 'password_change');

        return response()->json([
            'result' => true,
            'message' => translate('Password changed successfully. Please login again.'),
            'sessions_revoked' => true,
        ]);
    }

    public function socialLogin(Request $request)
    {
        if (!$request->provider) {
            return response()->json([
                'result' => false,
                'message' => translate('User not found'),
                'user' => null
            ]);
        }

        switch ($request->social_provider) {
            case 'facebook':
                $social_user = Socialite::driver('facebook')->fields([
                    'name',
                    'first_name',
                    'last_name',
                    'email'
                ]);
                break;
            case 'google':
                $social_user = Socialite::driver('google')
                    ->scopes(['profile', 'email']);
                break;
            case 'twitter':
                $social_user = Socialite::driver('twitter');
                break;
            case 'apple':
                $social_user = Socialite::driver('sign-in-with-apple')
                    ->scopes(['name', 'email']);
                break;
            default:
                $social_user = null;
        }
        if ($social_user == null) {
            return response()->json(['result' => false, 'message' => translate('No social provider matches'), 'user' => null]);
        }

        if ($request->social_provider == 'twitter') {
            $social_user_details = $social_user->userFromTokenAndSecret($request->access_token, $request->secret_token);
        } else {
            $social_user_details = $social_user->userFromToken($request->access_token);
        }

        if ($social_user_details == null) {
            return response()->json(['result' => false, 'message' => translate('No social account matches'), 'user' => null]);
        }

        $existingUserByProviderId = User::where('provider_id', $request->provider)->first();

        if ($existingUserByProviderId) {
            $existingUserByProviderId->access_token = $social_user_details->token;
            if ($request->social_provider == 'apple') {
                $existingUserByProviderId->refresh_token = $social_user_details->refreshToken;
                if (!isset($social_user->user['is_private_email'])) {
                    $existingUserByProviderId->email = $social_user_details->email;
                }
            }
            $existingUserByProviderId->save();
            return $this->loginSuccess($existingUserByProviderId);
        } else {
            $existing_or_new_user = User::firstOrNew(
                [['email', '!=', null], 'email' => $social_user_details->email]
            );

            // $existing_or_new_user->user_type = 'customer';
            $existing_or_new_user->provider_id = $social_user_details->id;

            if (!$existing_or_new_user->exists) {
                if ($request->social_provider == 'apple') {
                    if ($request->name) {
                        $existing_or_new_user->name = $request->name;
                    } else {
                        $existing_or_new_user->name = 'Apple User';
                    }
                } else {
                    $existing_or_new_user->name = $social_user_details->name;
                }
                $existing_or_new_user->email = $social_user_details->email;
                $existing_or_new_user->email_verified_at = date('Y-m-d H:m:s');
            }

            $existing_or_new_user->save();

            return $this->loginSuccess($existing_or_new_user);
        }
    }

    // Guest user Account Create
    public function guestUserAccountCreate(Request $request)
    {
        $success = 1;
        $password = substr(hash('sha512', rand()), 0, 8);
        $isEmailVerificationEnabled = get_setting('email_verification');

        // User Create
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = addon_is_activated('otp_system') ? $request->phone : null;
        $user->password = Hash::make($password);
        $user->email_verified_at = $isEmailVerificationEnabled != 1 ? date('Y-m-d H:m:s') : null;
        $user->save();

        // Account Opening and verification(if activated) eamil send
        try {
            EmailUtility::customer_registration_email('registration_from_system_email_to_customer', $user, $password);
        } catch (\Exception $e) {
            $success = 0;
            $user->delete();
        }

        if($success == 0){
            return response()->json([
                'result' => false,
                'message' => translate('Something went wrong!')
            ]);
        }

        if($isEmailVerificationEnabled == 1){
            $user->notify(new AppEmailVerificationNotification());
        }
        
        // User Address Create
        $address = new Address();
        $address->user_id       = $user->id;
        $address->address       = $request->address;
        $address->country_id    = $request->country_id;
        $address->state_id      = $request->state_id;
        $address->city_id       = $request->city_id;
        $address->postal_code   = $request->postal_code;
        $address->phone         = $request->phone;
        $address->longitude     = $request->longitude;
        $address->latitude      = $request->latitude;
        $address->save();

        Cart::where('temp_user_id', $request->temp_user_id)
            ->update([
                'user_id' => $user->id,
                'temp_user_id' => null,
                'address_id' => $address->id
            ]);

        //create token
        $user->createToken('tokens')->plainTextToken;

        return $this->loginSuccess($user);
    }

    public function loginSuccess($user, $token = null, $tempUserId = null)
    {

        if (!$token) {
            $token = $user->createToken('API Token')->plainTextToken;
        }

        if($tempUserId != null){
            Cart::where('temp_user_id', $tempUserId)
                ->update([
                    'user_id' => $user->id,
                    'temp_user_id' => null
                ]);
        }

         if($user->user_type == 'seller'){
            \Log::channel('seller_login')->info('Seller Logged In', [
                'user_id' => $user->id,
                'email' => $user->email,
                'time' => now()->toDateTimeString()
            ]);
        }

        return response()->json([
            'result' => true,
            'message' => translate('Successfully logged in'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => null,
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => uploaded_asset($user->avatar_original),
                'phone' => $user->phone,
                'email_verified' => $user->email_verified_at != null
            ]
        ]);
    }


    protected function loginFailed()
    {

        return response()->json([
            'result' => false,
            'message' => translate('Login Failed'),
            'access_token' => '',
            'token_type' => '',
            'expires_at' => null,
            'user' => [
                'id' => 0,
                'type' => '',
                'name' => '',
                'email' => '',
                'avatar' => '',
                'avatar_original' => '',
                'phone' => ''
            ]
        ]);
    }


    public function account_deletion()
    {
        if (auth()->user()) {
            Cart::where('user_id', auth()->user()->id)->delete();
        }
        $auth_user = auth()->user();
        $auth_user->tokens()->where('id', $auth_user->currentAccessToken()->id)->delete();
        $auth_user->customer_products()->delete();

        User::destroy(auth()->user()->id);

        return response()->json([
            "result" => true,
            "message" => translate('Your account deletion successfully done')
        ]);
    }


    public function getUserInfoByAccessToken(Request $request)
    {
        $token = PersonalAccessToken::findToken($request->access_token);
        if (!$token) {
            return $this->loginFailed();
        }
        $user = $token->tokenable;

        if ($user == null) {
            return $this->loginFailed();
        }

        return $this->loginSuccess($user, $request->access_token);
    }

    public function twoFactorSetup(Request $request)
    {
        $user = $request->user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        if (!$user->two_factor_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->two_factor_secret = encrypt($secret);
            $user->save();
        } else {
            $secret = decrypt($user->two_factor_secret);
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(config('app.name'), $user->email, $secret);

        return response()->json([
            'result' => true,
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'is_confirmed' => $user->hasTwoFactorEnabled(),
        ]);
    }

    public function twoFactorConfirm(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $secret = decrypt($user->two_factor_secret);

        if (!$google2fa->verifyKey($secret, $request->code)) {
            return response()->json(['result' => false, 'message' => translate('Invalid code.')], 422);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();
        $codes = $user->generateRecoveryCodes();

        return response()->json([
            'result' => true,
            'message' => translate('Two-factor authentication enabled.'),
            'recovery_codes' => $codes,
        ]);
    }

    public function twoFactorVerify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $secret = decrypt($user->two_factor_secret);

        if (!$google2fa->verifyKey($secret, $request->code)) {
            return response()->json(['result' => false, 'message' => translate('Invalid code.')], 422);
        }

        return response()->json(['result' => true, 'message' => translate('Verified.')]);
    }

    public function twoFactorDisable(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        $user = $request->user();

        if (!\Hash::check($request->password, $user->password)) {
            return response()->json(['result' => false, 'message' => translate('Invalid password.')], 422);
        }

        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return response()->json(['result' => true, 'message' => translate('Two-factor disabled.')]);
    }
}
