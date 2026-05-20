<?php

namespace App\Http\Controllers;

use App\Mail\ContactMailManager;
use App\Models\Contact;
use App\Models\User;
use App\Rules\Recaptcha;
use App\Rules\Turnstile;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mail;

class ContactController extends Controller
{
    public function __construct()
    {
        // Staff Permission Check
        $this->middleware(['permission:view_all_contacts'])->only('index');
        $this->middleware(['permission:reply_to_contact'])->only('reply_modal');
    }

    public function index()
    {
        $contacts = Contact::orderBy('id', 'desc')->paginate(20);
        return view('backend.support.contact.contacts', compact('contacts'));
    }

    public function query_modal(Request $request)
    {
        $contact = Contact::findOrFail($request->id);
        return view('backend.support.contact.query_modal', compact('contact'));
    }

    public function reply_modal(Request $request)
    {
        $contact = Contact::findOrFail($request->id);
        return view('backend.support.contact.reply_modal', compact('contact'));
    }

    public function reply(Request $request)
    {
        $contact = Contact::findOrFail($request->contact_id);
        $admin = get_admin();

        $array['name'] = $admin->name;
        $array['email'] = $admin->email;
        $array['phone'] = $admin->phone;
        $array['content'] = str_replace("\n", "<br>", $request->reply);
        $array['subject'] = translate('Query Contact Reply');
        $array['from'] = $admin->email;

        try {
            Mail::to($contact->email)->queue(new ContactMailManager($array));
            $contact->update([
                'reply' => $request->reply,
            ]);
        } catch (\Exception $e) {
            flash(translate('Something Went wrong'))->error();
            return back();
        }
        flash(translate('Reply has been sent successfully'))->success();
        return back();
    }

    public function contact(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'content' => ['required', 'string', 'max:10000'],
        ]);

        // validate recaptcha
        $request->validate([
            'g-recaptcha-response' => [
                Rule::when(get_setting('google_recaptcha') == 1 && get_setting('recaptcha_contact_form') == 1, ['required', new Recaptcha()], ['sometimes'])
            ],
        ]);

        // validate Cloudflare Turnstile
        $request->validate([
            'cf-turnstile-response' => [
                Rule::when(
                    get_setting('cloudflare_turnstile') == 1 && get_setting('turnstile_contact_form') == 1,
                    ['required', new Turnstile()],
                    ['sometimes']
                )
            ],
        ]);
        $admin = get_admin();
        if (!$admin || !$admin->email) {
            Log::warning('Contact form submission could not be delivered because no admin email is configured.');
            flash(translate('Something Went wrong'))->error();
            return back()->withInput();
        }

        $array['name'] = $request->name;
        $array['email'] = $request->email;
        $array['phone'] = $request->phone;
        $array['content'] = str_replace("\n", "<br>", $request->content);
        $array['subject'] = translate('Query Contact');
        $array['from'] = $request->email;

        /* EAI extended fields */
        $eaiData = [];
        if ($request->filled('eai_profile')) {
            $eaiData['profile'] = $request->eai_profile;
        }
        if ($request->filled('eai_request_type')) {
            $eaiData['request_type'] = $request->eai_request_type;
        }
        if ($request->filled('eai_city')) {
            $eaiData['city'] = $request->eai_city;
        }
        if ($request->filled('eai_project_stage')) {
            $eaiData['project_stage'] = $request->eai_project_stage;
        }
        if ($request->filled('eai_budget')) {
            $eaiData['budget'] = $request->eai_budget;
        }
        if ($request->filled('eai_timeline')) {
            $eaiData['timeline'] = $request->eai_timeline;
        }

        try {
            Mail::to($admin->email)->queue(new ContactMailManager($array));
            $contactData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'content' => $request->content,
            ];
            if (!empty($eaiData)) {
                $contactData['eai_data'] = json_encode($eaiData);
            }
            Contact::insert($contactData);
        } catch (\Exception $e) {
            Log::error('Contact form submission failed.', [
                'exception' => $e->getMessage(),
                'email' => $request->email,
            ]);
            flash(translate('Something Went wrong'))->error();
            return back();
        }
        flash(translate('Votre demande a bien été envoyée. Notre équipe vous contactera prochainement afin de clarifier votre besoin et vous orienter vers la solution la plus adaptée.'))->success();
        return back();
    }
}
