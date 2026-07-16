<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\Shop;
use App\Models\EliteSubscription;
use App\Models\Blog;
use App\Models\BlogSubscriberLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class TaskDashboardController extends Controller
{
    public function index()
    {
        // 1. Count of pending refunds
        $pendingRefunds = Schema::hasTable('refund_requests')
            ? RefundRequest::where('refund_status', 0)->count()
            : 0; 
        
        // 2. Sellers still restricted by the authoritative onboarding state
        $unverifiedSellers = Schema::hasTable('shops')
            ? Shop::whereIn('approval_status', ['pending', 'under_review', 'rejected'])->count()
            : 0;

        // 3. Failed or unpaid payments
        $failedPayments = Schema::hasTable('orders')
            ? Order::where('payment_status', 'unpaid')->count()
            : 0;

        // 4. Stalled orders (shipped but not delivered for 5+ days)
        $stalledOrders = Schema::hasTable('orders')
            ? Order::where('delivery_status', 'shipped')
                ->where('updated_at', '<', Carbon::now()->subDays(5))
                ->count()
            : 0;

        // 5. Subscriptions expiring soon (within 7 days)
        $expiringSubscriptions = 0;
        if (class_exists(EliteSubscription::class) && Schema::hasTable('elite_subscriptions')) {
            $expiringSubscriptions = EliteSubscription::where('status', 'active')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', Carbon::now()->addDays(7))
                ->where('expires_at', '>', Carbon::now())
                ->count();
        }

        $publishedBlogs = Schema::hasTable('blogs') ? Blog::where('status', 1)->count() : 0;
        $draftBlogs = Schema::hasTable('blogs') ? Blog::where('status', 0)->count() : 0;
        $featuredBlogs = Schema::hasTable('blogs') && Schema::hasColumn('blogs', 'is_featured')
            ? Blog::where('status', 1)->where('is_featured', 1)->count()
            : 0;
        $blogSubscribers = Schema::hasTable('blog_subscriber_logs') ? BlogSubscriberLog::count() : 0;

        return view('backend.task_dashboard.index', compact(
            'pendingRefunds',
            'unverifiedSellers',
            'failedPayments',
            'stalledOrders',
            'expiringSubscriptions',
            'publishedBlogs',
            'draftBlogs',
            'featuredBlogs',
            'blogSubscribers'
        ));
    }
}
