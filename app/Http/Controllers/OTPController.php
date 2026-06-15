<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OtpConfiguration;
use App\Models\BusinessSetting;
use Artisan;

class OTPController extends Controller
{
    /**
     * Display the OTP Login Configuration page.
     */
    public function loginConfigure()
    {
        return view('backend.otp_systems.index');
    }

    /**
     * Display the OTP Providers Configuration page.
     */
    public function configure_index()
    {
        $otp_configurations = OtpConfiguration::all();
        return view('backend.otp_systems.configurations', compact('otp_configurations'));
    }

    /**
     * Update activation settings for OTP providers.
     */
    public function updateActivationSettings(Request $request)
    {
        $otp_config = OtpConfiguration::where('type', $request->type)->first();
        if($otp_config != null){
            $otp_config->value = $request->value;
            $otp_config->save();
        }
        else{
            $otp_config = new OtpConfiguration;
            $otp_config->type = $request->type;
            $otp_config->value = $request->value;
            $otp_config->save();
        }

        Artisan::call('cache:clear');
        return 1;
    }

    /**
     * Update credentials for OTP providers in .env file.
     */
    public function update_credentials(Request $request)
    {
        foreach ($request->types as $key => $type) {
            $this->overWriteEnvFile($type, $request[$type]);
        }

        flash(translate('Settings updated successfully'))->success();
        return back();
    }

    /**
     * Helper to overwrite Env File values (mimics BusinessSettingsController logic).
     */
    public function overWriteEnvFile($type, $val)
    {
        if (env('DEMO_MODE') != 'On') {
            $path = base_path('.env');
            if (file_exists($path)) {
                $val = '"' . trim($val) . '"';
                $content = file_get_contents($path);
                if (preg_match("/^" . preg_quote($type) . "=.*/m", $content)) {
                    $new_content = preg_replace("/^" . preg_quote($type) . "=.*/m", $type . '=' . $val, $content);
                    file_put_contents($path, $new_content);
                } else {
                    file_put_contents($path, $content . "\r\n" . $type . '=' . $val);
                }
            }
        }
    }
}
