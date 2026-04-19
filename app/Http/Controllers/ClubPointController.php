<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClubPoint;
use App\Models\ClubPointDetail;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use App\Services\LoyaltyService;
use Auth;
use Session;

class ClubPointController extends Controller
{
    /**
     * Display the Loyalty Lounge / Club Points dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $loyaltyService = new LoyaltyService();

        $pointBalance = $loyaltyService->getPointBalance($user);
        $annualSpend  = $loyaltyService->getAnnualSpend($user);
        $tierProgress = $loyaltyService->getTierProgress($user);
        
        $tierLevel = $tierProgress['current_tier'] ? $tierProgress['current_tier']->tier_level : 0;
        $tierMeta  = LoyaltyService::getTierMeta($tierLevel);

        $pointHistory = $loyaltyService->getPointHistory($user, 20);

        return view('frontend.user.loyalty.hub', compact(
            'pointBalance',
            'annualSpend',
            'tierProgress',
            'tierLevel',
            'tierMeta',
            'pointHistory'
        ));
    }

    /**
     * Process points for an order.
     * Called by Helpers.php@calculateCommissionAffilationClubPoint
     */
    public function processClubPoints(Order $order)
    {
        $loyaltyService = new LoyaltyService();
        $user = $order->user;

        if (!$user) return;

        $total_points = 0;
        
        $clubPoint = ClubPoint::firstOrCreate(
            ['user_id' => $user->id],
            ['points' => 0]
        );

        foreach ($order->orderDetails as $orderDetail) {
            $product = $orderDetail->product;
            if ($product && $product->earn_point > 0) {
                // Calculate points using LoyaltyService (includes tier multipliers)
                $points = $loyaltyService->getPotentialPoints($product, $user) * $orderDetail->quantity;
                
                if ($points > 0) {
                    $clubPointDetail = new ClubPointDetail();
                    $clubPointDetail->user_id = $user->id;
                    $clubPointDetail->club_point_id = $clubPoint->id;
                    $clubPointDetail->order_id = $order->id;
                    $clubPointDetail->product_id = $product->id;
                    $clubPointDetail->points = $points;
                    $clubPointDetail->save();

                    $total_points += $points;
                }
            }
        }

        if ($total_points > 0) {
            $clubPoint->points += $total_points;
            $clubPoint->save();
        }
    }

    /**
     * Convert points into wallet balance.
     */
    public function convert_into_wallet(Request $request)
    {
        $points = $request->points;
        $user = Auth::user();
        $clubPoint = $user->club_point;

        if (!$clubPoint || $clubPoint->points < $points) {
            flash(translate('Insufficient points!'))->error();
            return back();
        }

        $loyaltyService = new LoyaltyService();
        $amount = $loyaltyService->pointsToMonetaryValue($points);

        if ($amount <= 0) {
            flash(translate('Points are not enough for conversion.'))->error();
            return back();
        }

        // Deduct points
        $clubPoint->points -= $points;
        $clubPoint->save();

        // Add to wallet
        $user->balance += $amount;
        $user->save();

        // Record wallet transaction
        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->amount = $amount;
        $wallet->payment_method = 'Club Point Convert';
        $wallet->payment_details = translate('Converted ') . $points . translate(' points to wallet.');
        $wallet->save();

        flash(translate('Points converted to wallet successfully!'))->success();
        return back();
    }

    public function configure()
    {
        return view('backend.loyalty_points.templates');
    }
    
    public function update(Request $request)
    {
        // Admin settings update
        foreach ($request->types as $key => $type) {
            set_setting($type, $request[$type]);
        }
        flash(translate('Settings updated successfully'))->success();
        return back();
    }
}

