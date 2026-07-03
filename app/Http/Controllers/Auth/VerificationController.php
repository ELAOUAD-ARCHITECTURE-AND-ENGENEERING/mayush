<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\OTPVerificationController;
use App\Utility\EmailUtility;
use App\Rules\Recaptcha;
use Illuminate\Support\Facades\Cache;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
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
        //$this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        if ($request->user()->email != null) {
            return $request->user()->hasVerifiedEmail()
                            ? redirect($this->redirectPath())
                            : view('auth.'.safe_auth_layout_select().'.verify_email');
        }
        else {
            $otpController = new OTPVerificationController;
            $otpController->send_code($request->user());
            return redirect()->route('otp.verification');
        }
    }


    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath());
        }

        // 24h Rate Limiting (Max 5 attempts)
        $cacheKey = 'verification_resend_attempts_' . $request->user()->id;
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= 5) {
            flash(translate('You have reached the maximum number of resend attempts for today. Please try again after 24 hours.'))->error();
            return back();
        }

        // Recaptcha Validation
        if (get_setting('google_recaptcha') == 1) {
            $request->validate([
                'g-recaptcha-response' => [new Recaptcha]
            ]);
        }

        // Increment attempts
        Cache::put($cacheKey, $attempts + 1, now()->addDay());

        EmailUtility::email_verification($request->user(), $request->user()->user_type);

        return redirect()->route('verification.waiting')->with('resent', true);
    }

    public function waiting(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectPath());
        }
        
        return view('auth.'.safe_auth_layout_select().'.verification_waiting');
    }

    public function check_status(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'verified' => true,
                'redirect_url' => $request->user()->user_type == 'seller' ? route('seller.dashboard') : route('dashboard')
            ]);
        }

        return response()->json(['verified' => false]);
    }

    public function verification_confirmation($code){
        $user = User::where('verification_code', $code)->first();
        if($user != null){
            $user->email_verified_at = Carbon::now();
            $user->save();
            auth()->login($user, true);
            offerUserWelcomeCoupon();
            flash(translate('Your email has been verified successfully'))->success();
        }
        else {
            flash(translate('Sorry, we could not verifiy you. Please try again'))->error();
        }

        if($user != null && $user->user_type == 'seller') {
            return redirect()->route('seller.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
