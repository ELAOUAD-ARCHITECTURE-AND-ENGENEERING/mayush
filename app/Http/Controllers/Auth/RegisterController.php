<?php

namespace App\Http\Controllers\Auth;

use Cookie;
use Session;
use App\Models\Cart;
use App\Models\User;
use App\Rules\Recaptcha;
use App\Rules\Turnstile;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
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
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            'verification_method' => 'nullable|in:email,phone',
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
                if ($phone && $this->findCustomerByPhone($phone) !== null) {
                    $validator->errors()->add('phone', translate('The phone has already been taken.'));
                }
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
        $verificationMethod = $this->registrationVerificationMethod($data);

        $user = User::create([
            'name' => $data['name'] . (isset($data['l_name']) && $data['l_name'] !== '' ? ' ' . $data['l_name'] : ''),
            'email' => $verificationMethod === 'email'
                ? ($data['email'] ?? null)
                : $this->syntheticEmailForPhone($phone),
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
        $request->merge([
            'verification_method' => $this->registrationVerificationMethod($request->all()),
        ]);

        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        $this->guard()->login($user);

        if($request->verification_method == 'email'){
            $user->email_verified_at = date('Y-m-d H:m:s');
            $user->save();
            offerUserWelcomeCoupon();
            flash(translate('Registration successful.'))->success();
        }
        else {
            $user->email_verified_at = date('Y-m-d H:m:s');
            $user->save();
            offerUserWelcomeCoupon();
            flash(translate('Registration successful.'))->success();
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

    private function findCustomerByPhone(?string $phone): ?User
    {
        if ($phone === null) {
            return null;
        }

        return User::whereNotNull('phone')->get()->first(function (User $user) use ($phone) {
            return $user->phone === $phone;
        });
    }

    private function syntheticEmailForPhone(?string $phone): string
    {
        return 'phone-' . sha1((string) $phone) . '@phone.local';
    }
}
