<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountModeController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'mode' => ['required', Rule::in(['seller', 'buyer'])],
        ]);

        if (!can_switch_account_mode($request->user())) {
            abort(403);
        }

        $mode = $request->input('mode');
        $request->session()->put('account_mode', $mode);

        if ($mode === 'seller') {
            return redirect()->route('seller.dashboard');
        }

        return redirect()->route('dashboard');
    }
}
