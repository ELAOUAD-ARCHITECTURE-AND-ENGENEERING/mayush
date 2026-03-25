<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OtpService;
use App\Models\User;

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

    public function verify()
    {
        // Stub
    }
    
    public function resend()
    {
        // Stub
    }
}
