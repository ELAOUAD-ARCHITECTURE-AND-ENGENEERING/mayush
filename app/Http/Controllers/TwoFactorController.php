<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function setup(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        if (!$user->two_factor_secret) {
            $secret = $google2fa->generateSecretKey();
            $user->two_factor_secret = encrypt($secret);
            $user->save();
        } else {
            $secret = decrypt($user->two_factor_secret);
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('frontend.user.two_factor_settings', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret,
            'user' => $user,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);

        if (!$google2fa->verifyKey($secret, $request->code)) {
            return back()->with('error', translate('Invalid verification code. Please try again.'));
        }

        $user->two_factor_confirmed_at = now();
        $user->save();
        $recoveryCodes = $user->generateRecoveryCodes();

        return view('frontend.user.two_factor_recovery', [
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        $user = $request->user();

        if (!\Hash::check($request->password, $user->password)) {
            return back()->with('error', translate('Invalid password.'));
        }

        $user->two_factor_secret = null;
        $user->two_factor_confirmed_at = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return back()->with('success', translate('Two-factor authentication has been disabled.'));
    }

    public function challenge()
    {
        return view('auth.two_factor_challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string|size:6',
            'recovery_code' => 'nullable|string',
        ]);

        $user = $request->user();

        if ($request->code) {
            $google2fa = new Google2FA();
            $secret = decrypt($user->two_factor_secret);

            if (!$google2fa->verifyKey($secret, $request->code)) {
                return back()->with('error', translate('Invalid code.'));
            }
        } elseif ($request->recovery_code) {
            if (!$user->useRecoveryCode(strtoupper($request->recovery_code))) {
                return back()->with('error', translate('Invalid recovery code.'));
            }
        } else {
            return back()->with('error', translate('Please provide a code.'));
        }

        session(['two_factor_verified' => true]);

        return redirect()->intended(
            $user->user_type === 'admin' || $user->user_type === 'staff'
                ? route('admin.dashboard')
                : route('dashboard')
        );
    }
}
