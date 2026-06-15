<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsTemplate;

class SmsTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sms_templates = SmsTemplate::all();
        return view('backend.otp_systems.sms_templates', compact('sms_templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sms_template = new SmsTemplate;
        $sms_template->identifier = $request->identifier;
        $sms_template->sms_body = $request->sms_body;
        $sms_template->template_id = $request->template_id;
        $sms_template->save();

        flash(translate('SMS Template has been created.'))->success();
        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sms_template = SmsTemplate::findOrFail($id);
        $sms_template->identifier = $request->identifier;
        $sms_template->sms_body = $request->sms_body;
        $sms_template->template_id = $request->template_id;
        $sms_template->save();

        flash(translate('SMS Template has been updated.'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        SmsTemplate::destroy($id);
        flash(translate('SMS Template has been deleted.'))->success();
        return back();
    }
}
