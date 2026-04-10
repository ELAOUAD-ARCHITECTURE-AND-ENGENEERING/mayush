<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\Seller;
use App\Models\EliteSubscription;
use Carbon\Carbon;

class TaskDashboardController extends Controller
{
    public function index()
    {
        // 1. Count of pending refunds
        $pendingRefunds = RefundRequest::where('refund_status', 0)->count(); 
        
        // 2. Unverified sellers
        $unverifiedSellers = Seller::where('verification_status', 0)->count();

        // 3. Failed or unpaid payments
        $failedPayments = Order::where('payment_status', 'unpaid')->count();

        // 4. Stalled orders (shipped but not delivered for 5+ days)
        $stalledOrders = Order::where('delivery_status', 'shipped')
            ->where('updated_at', '<', Carbon::now()->subDays(5))
            ->count();

        // 5. Subscriptions expiring soon (within 7 days)
        $expiringSubscriptions = 0;
        if (class_exists(EliteSubscription::class)) {
            $expiringSubscriptions = EliteSubscription::where('status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', Carbon::now()->addDays(7))
                ->where('expires_at', '>', Carbon::now())
                ->count();
        }

        return view('backend.task_dashboard.index', compact(
            'pendingRefunds',
            'unverifiedSellers',
            'failedPayments',
            'stalledOrders',
            'expiringSubscriptions'
        ));
    }
}
