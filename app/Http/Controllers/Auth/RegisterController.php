<?php

namespace App\Http\Controllers\Auth;

use Cookie;
use Session;
use App\Models\Cart;
use App\Models\User;
use App\Rules\Recaptcha;
use App\Rules\Turnstile;
use App\Models\RegistrationVerificationCode;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Controllers\OTPVerificationController;
use App\Utility\EmailUtility;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $verificationMethod = $this->registrationVerificationMethod($data);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => [
                Rule::requiredIf($verificationMethod === 'email'),
                'nullable',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => [
                Rule::requiredIf($verificationMethod === 'phone'),
                'nullable',
                'string',
                'max:20',
            ],
            'country_code' => [
                Rule::requiredIf($verificationMethod === 'phone'),
                'nullable',
                'string',
                'max:10',
            ],
            'password' => 'required|string|min:6|confirmed',
            'verification_method' => 'nullable|in:email,phone',
            'code' => [
                Rule::requiredIf(get_setting('customer_registration_verify') == '1'),
                'nullable',
                'digits:6',
            ],
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_customer_register') == 1 , ['required', new Recaptcha()], ['sometimes'])
            ],
            'cf-turnstile-response' => [
                Rule::when(
                    get_setting('cloudflare_turnstile') == 1 && get_setting('turnstile_customer_register') == 1,
                    ['required', new Turnstile()],
                    ['sometimes']
                )
            ],
        ]);

        $validator->after(function ($validator) use ($data, $verificationMethod) {
            if ($verificationMethod === 'phone' && !empty($data['phone'])) {
                $phone = $this->formatRegistrationPhone($data['phone'], $data['country_code'] ?? null);
                if ($phone && User::where('phone', $phone)->exists()) {
                    $validator->errors()->add('phone', translate('The phone has already been taken.'));
                }
            }

            if (get_setting('customer_registration_verify') != '1') {
                return;
            }

            $code = $data['code'] ?? $data['verified_registration_code'] ?? null;
            if (!$code) {
                return;
            }

            $query = RegistrationVerificationCode::where('code', $code)->where('is_verified', 1);
            if ($verificationMethod === 'email') {
                $query->where('email', $data['email'] ?? null);
            } else {
                $query->where('phone', $this->formatRegistrationPhone($data['phone'] ?? null, $data['country_code'] ?? null));
            }

            if (!$query->exists()) {
                $validator->errors()->add('code', translate('Please verify your email or phone before creating the account.'));
            }
        });

        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        if(get_setting('portfolio_landing') && get_setting('customer_verification')){
            $data['verification_status'] = 0;
        }

        $phone = $this->formatRegistrationPhone($data['phone'] ?? null, $data['country_code'] ?? null);

        $user = User::create([
            'name' => $data['name'] . (isset($data['l_name']) && $data['l_name'] !== '' ? ' ' . $data['l_name'] : ''),
            'email' => $data['email'] ?? null,
            'phone' => $phone,
            'password' => Hash::make($data['password']),
            'verification_code' => rand(100000, 999999),
            'verification_status' => $data['verification_status'] ?? 1
        ]);

        if(session('temp_user_id') != null){
            if(auth()->user()->user_type == 'customer'){
                Cart::where('temp_user_id', session('temp_user_id'))
                ->update(
                    [
                        'user_id' => auth()->user()->id,
                        'temp_user_id' => null
                    ]
                );
            }
            else {
                Cart::where('temp_user_id', session('temp_user_id'))->delete();
            }
            Session::forget('temp_user_id');
        }

        if(Cookie::has('referral_code')){
            $referral_code = Cookie::get('referral_code');
            $referred_by_user = User::where('referral_code', $referral_code)->first();
            if($referred_by_user != null){
                $user->referred_by = $referred_by_user->id;
                $user->save();
            }
        }



        return $user;
    }

    public function register(Request $request)
    {
        if (!$request->filled('code') && $request->filled('verified_registration_code')) {
            $request->merge(['code' => $request->verified_registration_code]);
        }

        $request->merge([
            'verification_method' => $this->registrationVerificationMethod($request->all()),
        ]);

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $this->guard()->login($user);

        if($request->verification_method == 'email'){
            if(BusinessSetting::where('type', 'email_verification')->first()->value != 1 || get_setting('customer_registration_verify') === '1'){
                $user->email_verified_at = date('Y-m-d H:m:s');
                $user->save();
                offerUserWelcomeCoupon();
                flash(translate('Registration successful.'))->success();
            }
            else {
                try {
                    EmailUtility::email_verification($user, 'customer');
                    flash(translate('Registration successful. Please verify your email.'))->success();
                } catch (\Throwable $e) {
                    $user->delete();
                    flash(translate('Registration failed. Please try again later.'))->error();
                    return back();
                }
            }
        }
        else {
            if(get_setting('customer_registration_verify') != '1' ){
                try {
                    $otpController = new OTPVerificationController;
                    $otpController->send_code($user);
                    flash(translate('Registration successful. Please verify your phone.'))->success();
                } catch (\Throwable $e) {
                    $user->delete();
                    flash(translate('Registration failed. Please try again later.'))->error();
                    return back();
                }
            } else {
                $user->email_verified_at = date('Y-m-d H:m:s');
                $user->save();
                offerUserWelcomeCoupon();
                flash(translate('Registration successful.'))->success();
            }
        }

        // Account Opening Email to customer
        if ( $user != null && (get_email_template_data('registration_email_to_customer', 'status') == 1)) {
            try {
                EmailUtility::customer_registration_email('registration_email_to_customer', $user, null);
            } catch (\Exception $e) {}
        }

        // customer Account Opening Email to Admin
        if ( $user != null && (get_email_template_data('customer_reg_email_to_admin', 'status') == 1)) {
            try {
                EmailUtility::customer_registration_email('customer_reg_email_to_admin', $user, null);
            } catch (\Exception $e) {}
        }

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    protected function registered(Request $request, $user)
    {
        if ($user->email_verified_at == null) {
            return redirect()->route('verification');
        }elseif(session('link') != null){
            return redirect(session('link'));
        }else {
            if(get_setting('portfolio_landing') && get_setting('customer_verification')){
                return redirect()->route('dashboard');
            }
            return redirect()->route('home');
        }
    }

    private function registrationVerificationMethod(array $data): string
    {
        if (!empty($data['verification_method']) && in_array($data['verification_method'], ['email', 'phone'])) {
            return $data['verification_method'];
        }

        if (!empty($data['email'])) {
            return 'email';
        }

        return 'phone';
    }

    private function formatRegistrationPhone(?string $phone, ?string $countryCode): ?string
    {
        $cleanPhone = preg_replace('/\D+/', '', $phone ?? '');
        $cleanCountryCode = preg_replace('/\D+/', '', $countryCode ?? '');

        if ($cleanPhone === '') {
            return null;
        }

        return '+' . $cleanCountryCode . $cleanPhone;
    }
}
