<?php

namespace App\Http\Controllers;

use App\Models\PaymentInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentInformationController extends Controller
{
    public function create()
    {
        return view('frontend.partials.payment_information.payment_information_modal');
    }

    public function ajax_create()
    {
        return view('frontend.partials.payment_information.ajax_payment_information_modal');
    }

    public function store(Request $request)
    {
        $this->createPaymentInformation($request);
        flash(translate('Payment info stored successfully'))->success();

        return back();
    }

    public function ajax_store(Request $request)
    {
        $this->createPaymentInformation($request);

        return $this->paymentInfoListResponse(translate('Payment info stored successfully'));
    }

    public function edit(Request $request)
    {
        $paymentInformation = $this->ownedPaymentInformation($request->payment_information_id);

        return view('frontend.partials.payment_information.payment_information_edit_modal', [
            'payment_information' => $paymentInformation,
        ]);
    }

    public function ajax_edit(Request $request)
    {
        $paymentInformation = $this->ownedPaymentInformation($request->payment_information_id);

        return view('frontend.partials.payment_information.ajax_payment_information_edit_modal', [
            'payment_information' => $paymentInformation,
        ]);
    }

    public function ajax_list()
    {
        return view('frontend.partials.payment_information.payment_info', [
            'payment_information_id' => $this->defaultPaymentInformationId(),
        ]);
    }

    public function update(Request $request)
    {
        $paymentInformation = $this->ownedPaymentInformation($request->payment_information_id);
        $paymentInformation->update($this->validatedData($request));

        flash(translate('Payment information updated successfully'))->success();
        return back();
    }

    public function ajax_update(Request $request)
    {
        $paymentInformation = $this->ownedPaymentInformation($request->payment_information_id);
        $paymentInformation->update($this->validatedData($request));

        return $this->paymentInfoListResponse(translate('Payment information updated successfully'));
    }

    public function destroy($id)
    {
        $paymentInformation = $this->ownedPaymentInformation($id);

        if ($paymentInformation->set_default) {
            flash(translate('Default payment information cannot be deleted'))->warning();
            return back();
        }

        $paymentInformation->delete();
        flash(translate('Payment information deleted successfully'))->success();
        return back();
    }

    public function set_default($id)
    {
        $paymentInformation = $this->ownedPaymentInformation($id);

        Auth::user()->payment_informations()->update(['set_default' => false]);
        $paymentInformation->update(['set_default' => true]);

        flash(translate('Default payment information updated successfully'))->success();
        return back();
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'payment_type' => ['nullable', 'string', 'max:100'],
            'payment_name' => ['nullable', 'string', 'max:255'],
            'other_payment_method' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'other_bank_name' => ['nullable', 'string', 'max:255'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'routing_number' => ['nullable', 'string', 'max:255'],
        ]);

        return [
            'payment_type' => $data['payment_type'] ?? null,
            'payment_name' => ($data['payment_name'] ?? null) === 'other_method'
                ? ($data['other_payment_method'] ?? null)
                : ($data['payment_name'] ?? null),
            'bank_name' => ($data['bank_name'] ?? null) === 'other_bank'
                ? ($data['other_bank_name'] ?? null)
                : ($data['bank_name'] ?? null),
            'payment_instruction' => $data['payment_instructions'] ?? null,
            'account_name' => $data['account_name'],
            'account_number' => $data['account_number'],
            'routing_number' => $data['routing_number'] ?? null,
        ];
    }

    private function ownedPaymentInformation($id): PaymentInformation
    {
        return Auth::user()->payment_informations()->whereKey($id)->firstOrFail();
    }

    private function createPaymentInformation(Request $request): PaymentInformation
    {
        $paymentInformation = new PaymentInformation($this->validatedData($request));
        $paymentInformation->user_id = Auth::id();
        $paymentInformation->set_default = !Auth::user()->payment_informations()->exists();
        $paymentInformation->save();

        return $paymentInformation;
    }

    private function defaultPaymentInformationId()
    {
        return Auth::user()->payment_informations()->where('set_default', true)->value('id')
            ?: Auth::user()->payment_informations()->value('id');
    }

    private function paymentInfoListResponse(string $message)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'html' => view('frontend.partials.payment_information.payment_info', [
                'payment_information_id' => $this->defaultPaymentInformationId(),
            ])->render(),
        ]);
    }
}
