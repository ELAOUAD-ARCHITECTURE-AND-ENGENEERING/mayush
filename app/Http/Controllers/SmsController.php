<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Utility\SendSMSUtility;

class SmsController extends Controller
{
    /**
     * Display the Bulk SMS page.
     */
    public function index()
    {
        return view('backend.otp_systems.sms_index');
    }

    /**
     * Send SMS to users.
     */
    public function send(Request $request)
    {
        $users = [];
        if ($request->has('user_emails')) {
            $users = User::whereIn('email', $request->user_emails)->get();
        }

        if ($request->has('mobile_numbers')) {
            $numbers = explode(',', $request->mobile_numbers);
            foreach ($numbers as $number) {
                SendSMSUtility::sendSMS(trim($number), get_setting('site_name'), $request->content);
            }
        }

        foreach ($users as $user) {
            if ($user->phone != null) {
                SendSMSUtility::sendSMS($user->phone, get_setting('site_name'), $request->content);
            }
        }

        flash(translate('SMS has been sent.'))->success();
        return back();
    }
}
