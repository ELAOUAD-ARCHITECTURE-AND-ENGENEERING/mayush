<?php

namespace App\Http\Controllers;

use App\Models\FollowSeller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowSellerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $followed_sellers = FollowSeller::where('user_id', Auth::user()->id)->orderBy('shop_id', 'asc')->paginate(10);
        return view('frontend.user.customer.followed_sellers', compact('followed_sellers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:shops,id'],
        ]);

        if (isCustomer()) {
            FollowSeller::firstOrCreate([
                'user_id' => Auth::id(),
                'shop_id' => $validated['id'],
            ]);

            flash(translate('Seller is followed Successfully'))->success();
            return back();
        }
        flash(translate('You need to login as a customer to follow this seller'))->success();
        return back();
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:shops,id'],
        ]);

        FollowSeller::where('user_id', Auth::id())
            ->where('shop_id', $validated['id'])
            ->delete();

        flash(translate('Seller is unfollowed Successfully'))->success();
        return back();
    }
}
